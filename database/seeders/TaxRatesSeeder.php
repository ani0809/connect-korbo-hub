<?php

namespace Database\Seeders;

use App\Models\Account;
use App\Models\TaxRate;
use Illuminate\Database\Seeder;

class TaxRatesSeeder extends Seeder
{
    public function run(): void
    {
        $vatAccountId = Account::query()->where('code', '2101')->value('id');
        $aitAccountId = Account::query()->where('code', '2102')->value('id');

        TaxRate::query()->updateOrCreate(
            ['name' => 'VAT 15%'],
            [
                'rate' => 15.00,
                'type' => 'exclusive',
                'applies_to' => 'both',
                'is_compound' => false,
                'account_id' => $vatAccountId,
                'is_active' => true,
                'is_default' => true,
            ]
        );

        TaxRate::query()->updateOrCreate(
            ['name' => 'AIT 5%'],
            [
                'rate' => 5.00,
                'type' => 'exclusive',
                'applies_to' => 'purchases',
                'is_compound' => false,
                'account_id' => $aitAccountId,
                'is_active' => true,
                'is_default' => false,
            ]
        );
    }
}

