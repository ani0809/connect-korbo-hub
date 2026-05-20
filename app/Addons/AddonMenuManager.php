<?php

namespace App\Addons;

class AddonMenuManager
{
    private array $items = [];

    public function register(string $addonId, array $menuItems): void
    {
        $this->items[$addonId] = $menuItems;
    }

    public function all(): array
    {
        return $this->items;
    }
}
