<?php

namespace Database\Seeders;

use App\Models\Currency;
use Illuminate\Database\Seeder;

class CurrenciesSeeder extends Seeder
{
    public function run(): void
    {
        $currencies = [
            ['name'=>'US Dollar','code'=>'USD','symbol'=>'$','exchange_rate'=>1,'is_default'=>true],
            ['name'=>'Bangladeshi Taka','code'=>'BDT','symbol'=>'Tk','exchange_rate'=>110],
            ['name'=>'Euro','code'=>'EUR','symbol'=>'EUR','exchange_rate'=>0.92],
            ['name'=>'British Pound','code'=>'GBP','symbol'=>'GBP','exchange_rate'=>0.79],
        ];
        foreach ($currencies as $currency) {
            Currency::query()->updateOrCreate(['code' => $currency['code']], array_merge([
                'symbol_position' => 'before',
                'decimal_separator' => '.',
                'thousand_separator' => ',',
                'decimal_places' => 2,
                'is_active' => true,
            ], $currency));
        }
    }
}
