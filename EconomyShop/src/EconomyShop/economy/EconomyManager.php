<?php
declare(strict_types=1);
namespace EconomyShop\economy;
use EconomyShop\database\EconomyDatabase;
use pocketmine\player\Player;
class EconomyManager {
    private EconomyDatabase $db;
    private array $cache = [];
    private float $startingMoney, $maxMoney, $payTax;
    public function __construct(EconomyDatabase $db, array $config) {
        $this->db = $db;
        $this->startingMoney = (float) ($config["starting-money"] ?? 1000.0);
        $this->maxMoney = (float) ($config["max-money"] ?? 0);
        $this->payTax = (float) ($config["pay-tax"] ?? 0);
    }
    public function loadPlayer(Player $player): void {
        $uuid = $player->getUniqueId()->toString();
        $money = $this->db->getMoney($uuid);
        if ($money === 0.0 && $this->startingMoney > 0) {
            $this->db->setMoney($uuid, $this->startingMoney);
            $money = $this->startingMoney;
        }
        $this->cache[$uuid] = $money;
    }
    public function savePlayer(Player $player): void {
        $uuid = $player->getUniqueId()->toString();
        if (isset($this->cache[$uuid])) {
            $this->db->setMoney($uuid, $this->cache[$uuid]);
            unset($this->cache[$uuid]);
        }
    }
    public function getMoney(Player $player): float {
        $uuid = $player->getUniqueId()->toString();
        return $this->cache[$uuid] ?? $this->db->getMoney($uuid);
    }
    public function setMoney(Player $player, float $amount): void {
        $uuid = $player->getUniqueId()->toString();
        if ($this->maxMoney > 0 && $amount > $this->maxMoney) $amount = $this->maxMoney;
        $this->cache[$uuid] = $amount;
        $this->db->setMoney($uuid, $amount);
    }
    public function addMoney(Player $player, float $amount): void { $this->setMoney($player, $this->getMoney($player) + $amount); }
    public function removeMoney(Player $player, float $amount): bool {
        if ($this->getMoney($player) < $amount) return false;
        $this->setMoney($player, $this->getMoney($player) - $amount);
        return true;
    }
    public function pay(Player $sender, Player $receiver, float $amount): bool {
        if ($amount <= 0) return false;
        $tax = $amount * ($this->payTax / 100);
        if (!$this->removeMoney($sender, $amount + $tax)) return false;
        $this->addMoney($receiver, $amount);
        return true;
    }
    public function getTopBalances(int $limit): array { return $this->db->getTopBalances($limit); }
    public function getPayTax(): float { return $this->payTax; }
    public function saveAll(): void {
        foreach ($this->cache as $uuid => $money) $this->db->setMoney($uuid, $money);
        $this->cache = [];
    }
}
