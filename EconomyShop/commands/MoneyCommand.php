<?php

declare(strict_types=1);

namespace EconomyShop\commands;

use EconomyShop\economy\EconomyManager;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;

class MoneyCommand extends Command {

    private EconomyManager $economy;

    public function __construct(EconomyManager $economy) {
        parent::__construct("money", "Xem số dư của bạn", "/money", ["balance", "bal"]);
        $this->economy = $economy;
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args): bool {
        if (!$sender instanceof Player) {
            $sender->sendMessage("Chỉ người chơi mới sử dụng lệnh này.");
            return true;
        }
        $money = $this->economy->getMoney($sender);
        $symbol = "xu"; // Có thể lấy từ config nếu cần
        $sender->sendMessage("§aSố dư của bạn: §e" . number_format($money, 0, '.', ',') . " §a$symbol");
        return true;
    }
}
