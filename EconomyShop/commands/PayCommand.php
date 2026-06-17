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
        $this->economy = $economy;
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args): bool {
        if (!$sender instanceof Player) {
            $sender->sendMessage("Chỉ người chơi mới sử dụng lệnh này.");
            return true;
        }
        if (count($args) < 2) {
            $sender->sendMessage("§cCách dùng: /pay <player> <amount>");
            return false;
        }
        $targetName = $args[0];
        $amount = (float) $args[1];
        if ($amount <= 0) {
            $sender->sendMessage("§cSố tiền phải lớn hơn 0.");
            return false;
        }
        $target = Server::getInstance()->getPlayerByPrefix($targetName);
        if (!$target) {
            $sender->sendMessage("§cKhông tìm thấy người chơi $targetName.");
            return false;
        }
        if ($target->getName() === $sender->getName()) {
            $sender->sendMessage("§cBạn không thể chuyển tiền cho chính mình.");
            return false;
        }
        if ($this->economy->pay($sender, $target, $amount)) {
            $symbol = "xu";
            $sender->sendMessage("§aBạn đã chuyển §e$amount $symbol §ađến cho §b" . $target->getName() . "§a.");
            $target->sendMessage("§aBạn nhận được §e$amount $symbol §atừ §b" . $sender->getName() . "§a.");
            // Thông báo thuế nếu có
            $tax = $this->economy->getPayTax();
            if ($tax > 0) {
                $taxAmount = $amount * ($tax / 100);
                $sender->sendMessage("§7(Phí chuyển: $taxAmount $symbol)");
            }
            return true;
        } else {
            $sender->sendMessage("§cBạn không đủ tiền để chuyển (bao gồm phí).");
            return false;
        }
    }
}