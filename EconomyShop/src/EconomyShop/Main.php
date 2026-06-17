<?php
declare(strict_types=1);
namespace EconomyShop;
use EconomyShop\database\EconomyDatabase;
use EconomyShop\economy\EconomyManager;
use EconomyShop\shop\ShopManager;
use EconomyShop\gui\ShopGUI;
use EconomyShop\commands\MoneyCommand;
use EconomyShop\commands\PayCommand;
use EconomyShop\commands\BaltopCommand;
use EconomyShop\commands\SellCommand;
use EconomyShop\commands\ShopAdminCommand;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\plugin\PluginBase;
class Main extends PluginBase implements Listener {
    private EconomyDatabase $database;
    private EconomyManager $economy;
    private ShopManager $shop;
    private ShopGUI $shopGui;
    public function onEnable(): void {
        $df = $this->getDataFolder();
        if (!is_dir($df)) mkdir($df, 0777, true);
        $cp = $df . "config.yml";
        if (!file_exists($cp)) $this->saveResource("config.yml");
        $cfg = yaml_parse_file($cp);
        $sp = $df . "shop.yml";
        if (!file_exists($sp)) $this->saveResource("shop.yml");
        $this->database = new EconomyDatabase($df . "economy.db");
        $this->economy = new EconomyManager($this->database, $cfg);
        $this->shop = new ShopManager($sp);
        $this->shopGui = new ShopGUI($this->shop, $this->economy);
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
        $map = $this->getServer()->getCommandMap();
        $map->register("es", new MoneyCommand($this->economy));
        $map->register("es", new PayCommand($this->economy));
        $map->register("es", new BaltopCommand($this->economy, (int)($cfg["baltop-limit"]??10)));
        $map->register("es", new SellCommand($this->economy, $this->shop));
        $map->register("es", new ShopAdminCommand($this->shop));
        $this->getLogger()->info("§aEconomyShop đã bật! OK.");
    }
    public function onDisable(): void {
        if (isset($this->economy)) $this->economy->saveAll();
        if (isset($this->database)) $this->database->close();
    }
    public function onJoin(PlayerJoinEvent $e): void { $this->economy->loadPlayer($e->getPlayer()); }
    public function onQuit(PlayerQuitEvent $e): void { $this->economy->savePlayer($e->getPlayer()); }
    public function onCommand(CommandSender $s, Command $c, string $l, array $a): bool {
        if ($c->getName() === "shop" && $s instanceof Player) { $this->shopGui->openMainMenu($s); return true; }
        return false;
    }
}
