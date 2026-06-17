<?php
declare(strict_types=1);
namespace EconomyShop\commands;
use EconomyShop\economy\EconomyManager;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\Server;
class BaltopCommand extends Command {
    private EconomyManager $e;
    private int $l;
    public function __construct(EconomyManager $e, int $l=10) {
        parent::__construct("baltop", "Bảng xếp hạng", "/baltop");
        $this->setPermission("economyshop.use");
        $this->e = $e; $this->l = $l;
    }
    public function execute(CommandSender $s, string $l, array $a): bool {
        $top = $this->e->getTopBalances($this->l);
        $s->sendMessage("§6===== Top giàu =====");
        if (empty($top)) { $s->sendMessage("§7Trống"); return true; }
        $i=1;
        foreach ($top as $r) {
            $p = Server::getInstance()->getPlayerByRawUUID($r["uuid"]);
            $n = $p ? $p->getName() : substr($r["uuid"],0,8);
            $s->sendMessage("§e$i. §f$n: §a".number_format((float)$r["money"],0,'.',',')." xu");
            $i++;
        }
        return true;
    }
}
