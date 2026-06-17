<?php
declare(strict_types=1);
namespace EconomyShop\commands;
use EconomyShop\shop\ShopManager;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
class ShopAdminCommand extends Command {
    private ShopManager $shop;
    public function __construct(ShopManager $shop) {
        parent::__construct("shopadmin", "Quản lý shop", "/shopadmin <add|remove|list|reload>");
        $this->setPermission("economyshop.admin");
        $this->shop = $shop;
    }
    public function execute(CommandSender $sender, string $commandLabel, array $args): bool {
        if (!$this->testPermission($sender)) return false;
        $sub = strtolower($args[0] ?? "");
        switch ($sub) {
            case "add":
                if (count($args) < 5) { $sender->sendMessage("§c/shopadmin add <category> <id> <buy> <sell> [name]"); return false; }
                $this->shop->addItem($args[1], $args[2], (float)$args[3], (float)$args[4], $args[5] ?? "");
                $sender->sendMessage("§aĐã thêm §e{$args[2]} §avào §e{$args[1]}");
                break;
            case "remove":
                if (count($args) < 3) { $sender->sendMessage("§c/shopadmin remove <category> <slot>"); return false; }
                if ($this->shop->removeItem($args[1], (int)$args[2])) $sender->sendMessage("§aĐã xóa.");
                else $sender->sendMessage("§cKhông tìm thấy.");
                break;
            case "list":
                $list = $this->shop->listAll();
                $sender->sendMessage("§6=== Danh sách ===");
                foreach ($list as $l) $sender->sendMessage("§e- §f$l");
                if (empty($list)) $sender->sendMessage("§7Trống.");
                break;
            case "reload":
                $this->shop->reload();
                $sender->sendMessage("§aĐã reload shop.yml");
                break;
            default:
                $sender->sendMessage("§c/shopadmin add|remove|list|reload");
                return false;
        }
        return true;
    }
}
