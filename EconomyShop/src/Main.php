<?php

declare(strict_types=1);

namespace TanPhat\EconomyShop;

use pocketmine\plugin\PluginBase;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;

class Main extends PluginBase {

    public function onEnable(): void {
        $this->getLogger()->info("§aEconomyShop đã bật!");
    }

    public function onDisable(): void {
        $this->getLogger()->info("§cEconomyShop đã tắt!");
    }

    public function onCommand(CommandSender $sender, Command $command, string $label, array $args): bool {
        switch ($command->getName()) {
            case "shop":
                if ($sender instanceof Player) {
                    $sender->sendMessage("§eTính năng shop đang được phát triển.");
                }
                return true;
            case "money":
                $sender->sendMessage("§eTính năng xem tiền đang phát triển.");
                return true;
            default:
                return false;
        }
    }
}