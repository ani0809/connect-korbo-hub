<?php

namespace App\Addons;

class HookManager
{
    private array $hooks = [];
    private array $filters = [];

    public function addAction(string $hook, callable $callback, int $priority = 10): void
    {
        $this->hooks[$hook][$priority][] = $callback;
    }

    public function doAction(string $hook, mixed ...$args): void
    {
        if (!isset($this->hooks[$hook])) return;
        ksort($this->hooks[$hook]);
        foreach ($this->hooks[$hook] as $callbacks) foreach ($callbacks as $cb) call_user_func_array($cb, $args);
    }

    public function addFilter(string $filter, callable $callback, int $priority = 10): void
    {
        $this->filters[$filter][$priority][] = $callback;
    }

    public function applyFilters(string $filter, mixed $value, mixed ...$args): mixed
    {
        if (!isset($this->filters[$filter])) return $value;
        ksort($this->filters[$filter]);
        foreach ($this->filters[$filter] as $callbacks) foreach ($callbacks as $cb) $value = call_user_func_array($cb, array_merge([$value], $args));
        return $value;
    }
}
