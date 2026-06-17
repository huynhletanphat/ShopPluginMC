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
    public function __construct(ShopManager $s, EconomyManager $e) { $this->shop = $s; $this->econ = $e; }
    public function openMainMenu(Player $p): void {
        $cats = $this->shop->getCategories();
        $p->sendForm(new class($this, $cats) implements Form {
            private ShopGUI $g; private array $c;
            public function __construct(ShopGUI $g, array $c) { $this->g = $g; $this->c = $c; }
            public function handleResponse(\pocketmine\player\Player $p, $d): void {
                if ($d===null) return;
                $ks = array_keys($this->c);
                if (isset($ks[$d])) $this->g->openCategory($p, $ks[$d]);
            }
            public function jsonSerialize(): array {
                $b = [];
                foreach ($this->c as $c) $b[] = ["text"=>$c["name"]];
                return ["type"=>"form","title"=>"§lCỬA HÀNG","content"=>"Chọn danh mục:","buttons"=>$b];
            }
        });
    }
    public function openCategory(Player $p, string $k): void {
        $cat = $this->shop->getCategory($k);
        if (!$cat) return;
        $p->sendForm(new class($this, $cat) implements Form {
            private ShopGUI $g; private array $c;
            public function __construct(ShopGUI $g, array $c) { $this->g = $g; $this->c = $c; }
            public function handleResponse(\pocketmine\player\Player $p, $d): void {
                if ($d===null) return;
                $its = $this->c["items"];
                if ($d >= count($its)) { $this->g->openMainMenu($p); return; }
                $it = $its[$d];
                $pr = (float)$it["buy"];
                if ($this->g->econ->removeMoney($p, $pr)) {
                    $is = StringToItemParser::getInstance()->parse($it["id"]);
                    if ($is) { $is->setCount(1); $p->getInventory()->addItem($is); }
                    $p->sendMessage("§aMua {$it["id"]} giá $pr xu!");
                } else $p->sendMessage("§cKhông đủ tiền! Cần $pr xu.");
            }
            public function jsonSerialize(): array {
                $b = [];
                foreach ($this->c["items"] as $it) $b[] = ["text"=>($it["name"]??$it["id"])."\n§aMua: {$it["buy"]} xu"];
                $b[] = ["text"=>"§cQuay lại"];
                return ["type"=>"form","title"=>$this->c["name"],"content"=>"Chọn item:","buttons"=>$b];
            }
        });
    }
}
