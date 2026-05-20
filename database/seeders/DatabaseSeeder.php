<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $seeders = [
            SettingsSeeder::class,
            PolicyPagesSeeder::class,
            LanguageSeeder::class,
            CurrenciesSeeder::class,
            PaymentGatewaysSeeder::class,
            NotificationTemplatesSeeder::class,
            SystemUpdateSeeder::class,
            AccountsSeeder::class,
            ExpenseCategoriesSeeder::class,
            TaxRatesSeeder::class,
        ];

        $this->call(array_values(array_filter($seeders, static fn ($seeder) => class_exists($seeder))));

        if (config('shop.demo_mode')) {
            if (class_exists(DemoSeeder::class)) {
                $this->call([DemoSeeder::class]);
            }
        }
    }
}
