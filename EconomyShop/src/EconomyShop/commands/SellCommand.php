<?php
declare(strict_types=1);
namespace EconomyShop\commands;
use EconomyShop\economy\EconomyManager;
use EconomyShop\shop\ShopManager;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\item\StringToItemParser;
class SellCommand extends Command {
    private EconomyManager $economy;
    private ShopManager $shop;
    public function __construct(EconomyManager $economy, ShopManager $shop) {
        parent::__construct("sell", "Bán vật phẩm", "/sell <hand|all>");
        $this->setPermission("economyshop.use");
        $this->economy = $economy;
        $this->shop = $shop;
    }
    public function execute(CommandSender $sender, string $commandLabel, array $args): bool {
        if (!$sender instanceof Player) { $sender->sendMessage("§cChỉ người chơi!"); return true; }
        $sub = strtolower($args[0] ?? "hand");
        if ($sub === "hand") $this->sellHand($sender);
        elseif ($sub === "all") $this->sellAll($sender);
        else $sender->sendMessage("§c/sell hand hoặc /sell all");
        return true;
    }
    private function sellHand(Player $p): void {
        $item = $p->getInventory()->getItemInHand();
        if ($item->isNull()) { $p->sendMessage("§cKhông cầm gì."); return; }
        $price = $this->getSellPrice($item->getTypeId());
        if ($price <= 0) { $p->sendMessage("§cKhông bán được."); return; }
        $total = $price * $item->getCount();
        $p->getInventory()->setItemInHand($item->setCount(0));
        $this->economy->addMoney($p, $total);
        $p->sendMessage("§aBán §e{$item->getCount()}x {$item->getName()} §ađược §e{$total} xu§a.");
    }
    private function sellAll(Player $p): void {
        $inv = $p->getInventory();
        $earned = 0.0; $sold = 0;
        foreach ($inv->getContents() as $slot => $item) {
            if ($item->isNull()) continue;
            $price = $this->getSellPrice($item->getTypeId());
            if ($price <= 0) continue;
            $earned += $price * $item->getCount();
            $sold += $item->getCount();
            $inv->setItem($slot, $item->setCount(0));
        }
        if ($sold === 0) { $p->sendMessage("§cKhông có gì để bán."); return; }
        $this->economy->addMoney($p, $earned);
        $p->sendMessage("§aBán §e{$sold} vật phẩm §ađược §e{$earned} xu§a.");
    }
    private function getSellPrice(int $typeId): float {
        foreach ($this->shop->getCategories() as $cat)
            foreach ($cat["items"] as $it)
                if (($parsed = StringToItemParser::getInstance()->parse($it["id"])) && $parsed->getTypeId() === $typeId)
                    return (float)($it["sell"]??0);
        return 0.0;
    }
}
