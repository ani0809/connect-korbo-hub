<?php

namespace Database\Seeders;

use App\Models\Account;
use App\Models\ExpenseCategory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ExpenseCategoriesSeeder extends Seeder
{
    public function run(): void
    {
        $map = [
            'Office Supplies' => '6004',
            'Rent' => '6002',
            'Utilities' => '6003',
            'Salaries' => '6001',
            'Facebook Ads' => '6101',
            'Google Ads' => '6101',
            'Courier Charges' => '6401',
            'Packaging' => '5004',
            'Bank Charges' => '6301',
            'Payment Gateway Fees' => '6204',
            'Repairs' => '6005',
            'Equipment Purchase' => '1401',
            'Professional Services' => '6503',
            'Travel' => '6403',
            'Training' => '6503',
            'Insurance' => '6503',
            'Miscellaneous' => '4202',
            'SMS Marketing' => '6102',
            'Hosting & Server' => '6202',
            'Domain Names' => '6203',
            'Legal Fees' => '6502',
            'Accounting & Audit' => '6501',
        ];

        foreach ($map as $name => $code) {
            ExpenseCategory::query()->updateOrCreate(
                ['name' => $name],
                [
                    'code' => Str::upper(Str::slug($name, '_')),
                    'account_id' => Account::query()->where('code', $code)->value('id'),
                    'color' => '#64748b',
                    'icon' => 'receipt',
                    'is_active' => true,
                ]
            );
        }
    }
}

