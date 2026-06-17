<?php
declare(strict_types=1);
namespace EconomyShop\gui;

use EconomyShop\shop\ShopManager;
use EconomyShop\economy\EconomyManager;
use pocketmine\player\Player;
use pocketmine\form\Form;
use pocketmine\item\StringToItemParser;

class ShopGUI {
    private ShopManager $shop;
    private EconomyManager $econ;

    public function __construct(ShopManager $s, EconomyManager $e) {
        $this->shop = $s;
        $this->econ = $e;
    }

    public function openMainMenu(Player $p): void {
        $cats = $this->shop->getCategories();
        $p->sendForm(new class($this, $cats) implements Form {
            private ShopGUI $g;
            private array $c;
            public function __construct(ShopGUI $g, array $c) { $this->g = $g; $this->c = $c; }
            public function handleResponse(\pocketmine\player\Player $p, $d): void {
                if ($d === null) return;
                $ks = array_keys($this->c);
                if (isset($ks[$d])) $this->g->openCategory($p, $ks[$d]);
            }
            public function jsonSerialize(): array {
                $b = [];
                foreach ($this->c as $cat) $b[] = ["text" => $cat["name"]];
                return ["type" => "form", "title" => "§lCỬA HÀNG", "content" => "Chọn danh mục:", "buttons" => $b];
            }
        });
    }

    public function openCategory(Player $p, string $k): void {
        $cat = $this->shop->getCategory($k);
        if (!$cat) return;
        $p->sendForm(new class($this, $cat) implements Form {
            private ShopGUI $g;
            private array $c;
            public function __construct(ShopGUI $g, array $c) { $this->g = $g; $this->c = $c; }
            public function handleResponse(\pocketmine\player\Player $p, $d): void {
                if ($d === null) return;
                $items = $this->c["items"];
                if ($d >= count($items)) { $this->g->openMainMenu($p); return; }
                $this->g->openQuantityForm($p, $items[$d]);
            }
            public function jsonSerialize(): array {
                $b = [];
                foreach ($this->c["items"] as $it) {
                    $name = $it["name"] ?? $it["id"];
                    $b[] = ["text" => "{$name}\n§aMua: {$it["buy"]} xu"];
                }
                $b[] = ["text" => "§cQuay lại"];
                return ["type" => "form", "title" => $this->c["name"], "content" => "Chọn item:", "buttons" => $b];
            }
        });
    }

    private function openQuantityForm(Player $p, array $itemData): void {
        $itemId = $itemData["id"];
        $itemName = $itemData["name"] ?? $itemId;
        $buyPrice = (float) $itemData["buy"];

        $item = StringToItemParser::getInstance()->parse($itemId);
        if ($item === null) {
            $p->sendMessage("§cItem không hợp lệ.");
            return;
        }
        $maxStack = $item->getMaxStackSize(); // 64, 16, 1...

        $p->sendForm(new class($this, $itemData, $itemId, $itemName, $buyPrice, $maxStack) implements Form {
            private ShopGUI $g;
            private array $itemData;
            private string $itemId;
            private string $itemName;
            private float $buyPrice;
            private int $maxStack;

            public function __construct(ShopGUI $g, array $itemData, string $itemId, string $itemName, float $buyPrice, int $maxStack) {
                $this->g = $g;
                $this->itemData = $itemData;
                $this->itemId = $itemId;
                $this->itemName = $itemName;
                $this->buyPrice = $buyPrice;
                $this->maxStack = $maxStack;
            }

            public function handleResponse(\pocketmine\player\Player $p, $data): void {
                if ($data === null) return;
                $quantity = (int) ($data[1] ?? 1);
                if ($quantity < 1 || $quantity > $this->maxStack) {
                    $p->sendMessage("§cSố lượng từ 1 đến {$this->maxStack}!");
                    return;
                }
                $totalPrice = $this->buyPrice * $quantity;
                if (!$this->g->econ->removeMoney($p, $totalPrice)) {
                    $p->sendMessage("§cKhông đủ tiền! Cần {$totalPrice} xu.");
                    return;
                }
                $item = StringToItemParser::getInstance()->parse($this->itemId);
                if ($item === null) {
                    $this->g->econ->addMoney($p, $totalPrice);
                    $p->sendMessage("§cLỗi tạo item.");
                    return;
                }
                $item->setCount($quantity);
                if (!$p->getInventory()->canAddItem($item)) {
                    $this->g->econ->addMoney($p, $totalPrice);
                    $p->sendMessage("§cTúi đồ không đủ chỗ.");
                    return;
                }
                $p->getInventory()->addItem($item);
                $p->sendMessage("§aĐã mua §e{$quantity}x {$this->itemName} §avới giá §e{$totalPrice} xu§a.");
            }

            public function jsonSerialize(): array {
                return [
                    "type" => "custom_form",
                    "title" => "Mua: {$this->itemName}",
                    "content" => [
                        ["type" => "label", "text" => "§eGiá mỗi item: §f{$this->buyPrice} xu\n§eTối đa: §f{$this->maxStack}"],
                        ["type" => "slider", "text" => "Số lượng", "min" => 1, "max" => $this->maxStack, "step" => 1, "default" => 1]
                    ]
                ];
            }
        });
    }

    public function getEconomy(): EconomyManager { return $this->econ; }
}
