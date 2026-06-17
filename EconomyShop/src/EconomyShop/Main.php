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
        $dataFolder = $this->getDataFolder();
        if (!is_dir($dataFolder)) mkdir($dataFolder, 0777, true);
        $configPath = $dataFolder . "config.yml";
        if (!file_exists($configPath)) $this->saveResource("config.yml");
        $config = yaml_parse_file($configPath);
        $shopPath = $dataFolder . "shop.yml";
        if (!file_exists($shopPath)) $this->saveResource("shop.yml");
        $this->database = new EconomyDatabase($dataFolder . "economy.db");
        $this->economy = new EconomyManager($this->database, $config);
        $this->shop = new ShopManager($shopPath);
        $this->shopGui = new ShopGUI($this->shop, $this->economy);
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
        $this->getServer()->getCommandMap()->register("economyshop", new MoneyCommand($this->economy));
        $this->getServer()->getCommandMap()->register("economyshop", new PayCommand($this->economy));
        $this->getServer()->getCommandMap()->register("economyshop", new BaltopCommand($this->economy, (int)($config["baltop-limit"] ?? 10)));
        $this->getLogger()->info("§aEconomyShop đã bật! Hệ thống hoàn chỉnh.");
    }
    public function onDisable(): void {
        if (isset($this->economy)) $this->economy->saveAll();
        if (isset($this->database)) $this->database->close();
    }
    public function onJoin(PlayerJoinEvent $event): void { $this->economy->loadPlayer($event->getPlayer()); }
    public function onQuit(PlayerQuitEvent $event): void { $this->economy->savePlayer($event->getPlayer()); }
    public function onCommand(CommandSender $sender, Command $command, string $label, array $args): bool {
        if ($command->getName() === "shop" && $sender instanceof Player) {
            $this->shopGui->openMainMenu($sender);
            return true;
        }
        return false;
    }
}
