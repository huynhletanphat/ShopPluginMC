<?php
declare(strict_types=1);
namespace EconomyShop\commands;
use EconomyShop\economy\EconomyManager;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\Server;
class PayCommand extends Command {
    private EconomyManager $economy;
    public function __construct(EconomyManager $economy) {
        parent::__construct("pay", "Chuyển tiền cho người chơi khác", "/pay <player> <amount>");
        $this->setPermission("economyshop.use");
        $this->economy = $economy;
    }
    public function execute(CommandSender $sender, string $commandLabel, array $args): bool {
        if (!$sender instanceof Player) { $sender->sendMessage("Chỉ người chơi mới sử dụng lệnh này."); return true; }
        if (count($args) < 2) { $sender->sendMessage("§c/pay <player> <amount>"); return false; }
        $target = Server::getInstance()->getPlayerByPrefix($args[0]);
        if (!$target || $target->getName() === $sender->getName()) { $sender->sendMessage("§cKhông hợp lệ."); return false; }
        $amount = (float) $args[1];
        if ($amount <= 0) { $sender->sendMessage("§cSố tiền > 0."); return false; }
        if ($this->economy->pay($sender, $target, $amount)) {
            $sender->sendMessage("§aĐã chuyển §e$amount xu §ađến §b" . $target->getName());
            $target->sendMessage("§aNhận §e$amount xu §atừ §b" . $sender->getName());
            return true;
        }
        $sender->sendMessage("§cKhông đủ tiền.");
        return false;
    }
}
