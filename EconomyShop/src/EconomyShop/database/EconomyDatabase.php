<?php
declare(strict_types=1);
namespace EconomyShop\database;
use SQLite3;
class EconomyDatabase {
    private SQLite3 $db;
    public function __construct(string $path) {
        $this->db = new SQLite3($path);
        $this->db->exec("CREATE TABLE IF NOT EXISTS balances (uuid TEXT PRIMARY KEY, money REAL NOT NULL DEFAULT 0.0)");
    }
    public function getMoney(string $uuid): float {
        $stmt = $this->db->prepare("SELECT money FROM balances WHERE uuid = :uuid");
        $stmt->bindValue(":uuid", $uuid);
        $result = $stmt->execute()->fetchArray(SQLITE3_ASSOC);
        return $result ? (float) $result["money"] : 0.0;
    }
    public function setMoney(string $uuid, float $amount): void {
        $stmt = $this->db->prepare("INSERT INTO balances (uuid, money) VALUES (:uuid, :amount) ON CONFLICT(uuid) DO UPDATE SET money = :amount");
        $stmt->bindValue(":uuid", $uuid); $stmt->bindValue(":amount", $amount); $stmt->execute();
    }
    public function addMoney(string $uuid, float $amount): void {
        $stmt = $this->db->prepare("INSERT INTO balances (uuid, money) VALUES (:uuid, :amount) ON CONFLICT(uuid) DO UPDATE SET money = money + :amount");
        $stmt->bindValue(":uuid", $uuid); $stmt->bindValue(":amount", $amount); $stmt->execute();
    }
    public function removeMoney(string $uuid, float $amount): bool {
        $current = $this->getMoney($uuid);
        if ($current < $amount) return false;
        $stmt = $this->db->prepare("UPDATE balances SET money = money - :amount WHERE uuid = :uuid");
        $stmt->bindValue(":uuid", $uuid); $stmt->bindValue(":amount", $amount); $stmt->execute();
        return true;
    }
    public function getTopBalances(int $limit): array {
        $stmt = $this->db->prepare("SELECT uuid, money FROM balances ORDER BY money DESC LIMIT :limit");
        $stmt->bindValue(":limit", $limit, SQLITE3_INTEGER);
        $result = $stmt->execute();
        $top = [];
        while ($row = $result->fetchArray(SQLITE3_ASSOC)) $top[] = $row;
        return $top;
    }
    public function close(): void { $this->db->close(); }
}
