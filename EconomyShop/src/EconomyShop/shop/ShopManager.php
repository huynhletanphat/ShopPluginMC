<?php
declare(strict_types=1);
namespace EconomyShop\shop;
class ShopManager {
    private array $cats = [];
    public function __construct(string $path) {
        $d = yaml_parse_file($path);
        $this->cats = $d["categories"] ?? [];
    }
    public function getCategories(): array { return $this->cats; }
    public function getCategory(string $k): ?array { return $this->cats[$k] ?? null; }
}
