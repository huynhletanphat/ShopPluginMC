<?php
declare(strict_types=1);
namespace EconomyShop\commands;
use EconomyShop\economy\EconomyManager;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\Server;
class BaltopCommand extends Command {
    private EconomyManager $economy;
    private int $limit;
    public function __construct(EconomyManager $economy, int $limit = 10) {
        parent::__construct("baltop", "Xem bảng xếp hạng giàu có", "/baltop");
        $this->setPermission("economyshop.use");
        $this->economy = $economy; $this->limit = $limit;
    }
    public function execute(CommandSender $sender, string $commandLabel, array $args): bool {
        $top = $this->economy->getTopBalances($this->limit);
        $sender->sendMessage("§6===== Bảng xếp hạng giàu có =====");
        if (empty($top)) { $sender->sendMessage("§7Chưa có dữ liệu."); return true; }
        $i = 1;
        foreach ($top as $row) {
            $p = Server::getInstance()->getPlayerByRawUUID($row["uuid"]);
            $name = $p ? $p->getName() : substr($row["uuid"], 0, 8) . "...";
            $sender->sendMessage("§e$i. §f$name: §a" . number_format((float)$row["money"], 0, '.', ',') . " xu");
            $i++;
        }
        return true;
    }
}
