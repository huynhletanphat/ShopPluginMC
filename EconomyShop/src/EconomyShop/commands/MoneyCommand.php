<?php
declare(strict_types=1);
namespace EconomyShop\commands;
use EconomyShop\economy\EconomyManager;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
class MoneyCommand extends Command {
    private EconomyManager $e;
    public function __construct(EconomyManager $e) {
        parent::__construct("money", "Xem số dư", "/money", ["balance","bal"]);
        $this->setPermission("economyshop.use");
        $this->e = $e;
    }
    public function execute(CommandSender $s, string $l, array $a): bool {
        if (!$s instanceof Player) { $s->sendMessage("Chỉ người chơi!"); return true; }
        $s->sendMessage("§aSố dư: §e".number_format($this->e->getMoney($s),0,'.',',')." §axu");
        return true;
    }
}
