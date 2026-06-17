<?php
declare(strict_types=1);
namespace EconomyShop\database;
use SQLite3;
class EconomyDatabase {
    private SQLite3 $db;
    public function __construct(string $path) {
        $this->db = new SQLite3($path);
        $this->db->exec("CREATE TABLE IF NOT EXISTS balances (uuid TEXT PRIMARY KEY, money REAL DEFAULT 0)");
    }
    public function getMoney(string $uuid): float {
        $r = $this->db->querySingle("SELECT money FROM balances WHERE uuid='$uuid'", true);
        return $r ? (float)$r["money"] : 0.0;
    }
    public function setMoney(string $uuid, float $amount): void {
        $this->db->exec("INSERT OR REPLACE INTO balances (uuid, money) VALUES ('$uuid', $amount)");
    }
    public function getTopBalances(int $limit): array {
        $r = $this->db->query("SELECT uuid, money FROM balances ORDER BY money DESC LIMIT $limit");
        $top = [];
        while ($row = $r->fetchArray(SQLITE3_ASSOC)) $top[] = $row;
        return $top;
    }
    public function close(): void { $this->db->close(); }
}
