<?php
declare(strict_types=1);
namespace EconomyShop\commands;
use EconomyShop\economy\EconomyManager;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\Server;
class PayCommand extends Command {
    private EconomyManager $e;
    public function __construct(EconomyManager $e) {
        parent::__construct("pay", "Chuyển tiền", "/pay <player> <amount>");
        $this->setPermission("economyshop.use");
        $this->e = $e;
    }
    public function execute(CommandSender $s, string $l, array $a): bool {
        if (!$s instanceof Player) { $s->sendMessage("Chỉ người chơi!"); return true; }
        if (count($a)<2) { $s->sendMessage("§c/pay <player> <amount>"); return false; }
        $t = Server::getInstance()->getPlayerByPrefix($a[0]);
        if (!$t || $t->getName()===$s->getName()) { $s->sendMessage("§cKhông hợp lệ"); return false; }
        $m = (float)$a[1];
        $tax = $m * ($this->e->getPayTax()/100);
        if ($this->e->removeMoney($s, $m+$tax)) {
            $this->e->addMoney($t, $m);
            $s->sendMessage("§aĐã chuyển §e$m xu §ađến §b".$t->getName());
            $t->sendMessage("§aNhận §e$m xu §atừ §b".$s->getName());
            return true;
        }
        $s->sendMessage("§cKhông đủ tiền!");
        return false;
    }
}
