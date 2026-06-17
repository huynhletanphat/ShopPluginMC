<?php
declare(strict_types=1);
namespace EconomyShop\shop;
class ShopManager {
    private array $cats = [];
    private string $path;
    public function __construct(string $path) { $this->path = $path; $this->load(); }
    private function load(): void {
        if (file_exists($this->path)) {
            $d = yaml_parse_file($this->path);
            $this->cats = $d["categories"] ?? [];
        }
    }
    public function getCategories(): array { return $this->cats; }
    public function getCategory(string $k): ?array { return $this->cats[$k] ?? null; }
    public function addItem(string $cat, string $id, float $buy, float $sell, string $name = ""): void {
        if (!isset($this->cats[$cat])) $this->cats[$cat] = ["name"=>"§l".ucfirst($cat),"icon"=>"stone","items"=>[]];
        $item = ["id"=>$id,"buy"=>$buy,"sell"=>$sell];
        if ($name !== "") $item["name"]=$name;
        $this->cats[$cat]["items"][] = $item;
        $this->save();
    }
    public function removeItem(string $cat, int $slot): bool {
        if (!isset($this->cats[$cat]["items"][$slot])) return false;
        array_splice($this->cats[$cat]["items"], $slot, 1);
        if (empty($this->cats[$cat]["items"])) unset($this->cats[$cat]);
        $this->save();
        return true;
    }
    public function reload(): void { $this->load(); }
    public function listAll(): array {
        $l = [];
        foreach ($this->cats as $k => $c) $l[] = "$k (".count($c["items"])." items)";
        return $l;
    }
    private function save(): void {
        file_put_contents($this->path, yaml_emit(["categories"=>$this->cats], YAML_UTF8_ENCODING));
    }
}
