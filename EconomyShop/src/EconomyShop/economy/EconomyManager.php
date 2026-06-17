<?php
declare(strict_types=1);
namespace EconomyShop\economy;
use EconomyShop\database\EconomyDatabase;
use pocketmine\player\Player;
class EconomyManager {
    private EconomyDatabase $db;
    private array $cache = [];
    private float $start, $tax;
    public function __construct(EconomyDatabase $db, array $c) {
        $this->db = $db;
        $this->start = (float)($c["starting-money"]??1000);
        $this->tax = (float)($c["pay-tax"]??0);
    }
    public function loadPlayer(Player $p): void {
        $u = $p->getUniqueId()->toString();
        $m = $this->db->getMoney($u);
        if ($m == 0 && $this->start > 0) { $this->db->setMoney($u, $this->start); $m = $this->start; }
        $this->cache[$u] = $m;
    }
    public function savePlayer(Player $p): void {
        $u = $p->getUniqueId()->toString();
        if (isset($this->cache[$u])) { $this->db->setMoney($u, $this->cache[$u]); unset($this->cache[$u]); }
    }
    public function getMoney(Player $p): float {
        $u = $p->getUniqueId()->toString();
        return $this->cache[$u] ?? $this->db->getMoney($u);
    }
    public function addMoney(Player $p, float $a): void {
        $u = $p->getUniqueId()->toString();
        $this->cache[$u] = $this->getMoney($p) + $a;
        $this->db->setMoney($u, $this->cache[$u]);
    }
    public function removeMoney(Player $p, float $a): bool {
        if ($this->getMoney($p) < $a) return false;
        $u = $p->getUniqueId()->toString();
        $this->cache[$u] = $this->getMoney($p) - $a;
        $this->db->setMoney($u, $this->cache[$u]);
        return true;
    }
    public function getTopBalances(int $l): array { return $this->db->getTopBalances($l); }
    public function getPayTax(): float { return $this->tax; }
    public function saveAll(): void { foreach ($this->cache as $u => $m) $this->db->setMoney($u, $m); $this->cache = []; }
}
