<?php

namespace Database\Seeders;

use App\Models\Account;
use Illuminate\Database\Seeder;

class AccountsSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            ['code' => '1000', 'name' => 'Cash and Cash Equivalents', 'type' => 'asset', 'subtype' => 'current_asset', 'parent' => null],
            ['code' => '1001', 'name' => 'Cash in Hand', 'type' => 'asset', 'subtype' => 'current_asset', 'parent' => '1000'],
            ['code' => '1002', 'name' => 'Cash in Bank (DBBL)', 'type' => 'asset', 'subtype' => 'current_asset', 'parent' => '1000'],
            ['code' => '1003', 'name' => 'Cash in Bank (bKash Business)', 'type' => 'asset', 'subtype' => 'current_asset', 'parent' => '1000'],
            ['code' => '1004', 'name' => 'Cash in Bank (Nagad Business)', 'type' => 'asset', 'subtype' => 'current_asset', 'parent' => '1000'],
            ['code' => '1100', 'name' => 'Accounts Receivable', 'type' => 'asset', 'subtype' => 'current_asset', 'parent' => null],
            ['code' => '1101', 'name' => 'Trade Receivables', 'type' => 'asset', 'subtype' => 'current_asset', 'parent' => '1100'],
            ['code' => '1102', 'name' => 'COD Receivables (Courier)', 'type' => 'asset', 'subtype' => 'current_asset', 'parent' => '1100'],
            ['code' => '1200', 'name' => 'Inventory', 'type' => 'asset', 'subtype' => 'current_asset', 'parent' => null],
            ['code' => '1201', 'name' => 'Product Inventory', 'type' => 'asset', 'subtype' => 'current_asset', 'parent' => '1200'],
            ['code' => '1202', 'name' => 'Goods in Transit', 'type' => 'asset', 'subtype' => 'current_asset', 'parent' => '1200'],
            ['code' => '1300', 'name' => 'Prepaid Expenses', 'type' => 'asset', 'subtype' => 'current_asset', 'parent' => null],
            ['code' => '1400', 'name' => 'Fixed Assets', 'type' => 'asset', 'subtype' => 'fixed_asset', 'parent' => null],
            ['code' => '1401', 'name' => 'Computer Equipment', 'type' => 'asset', 'subtype' => 'fixed_asset', 'parent' => '1400'],
            ['code' => '1402', 'name' => 'Office Furniture', 'type' => 'asset', 'subtype' => 'fixed_asset', 'parent' => '1400'],
            ['code' => '1403', 'name' => 'Vehicles', 'type' => 'asset', 'subtype' => 'fixed_asset', 'parent' => '1400'],
            ['code' => '1500', 'name' => 'Other Assets', 'type' => 'asset', 'subtype' => 'other_asset', 'parent' => null],
            ['code' => '1501', 'name' => 'Security Deposits', 'type' => 'asset', 'subtype' => 'other_asset', 'parent' => '1500'],

            ['code' => '2000', 'name' => 'Accounts Payable', 'type' => 'liability', 'subtype' => 'current_liability', 'parent' => null],
            ['code' => '2001', 'name' => 'Supplier Payables', 'type' => 'liability', 'subtype' => 'current_liability', 'parent' => '2000'],
            ['code' => '2002', 'name' => 'Seller Payouts Payable', 'type' => 'liability', 'subtype' => 'current_liability', 'parent' => '2000'],
            ['code' => '2100', 'name' => 'Tax Payable', 'type' => 'liability', 'subtype' => 'current_liability', 'parent' => null],
            ['code' => '2101', 'name' => 'VAT Payable', 'type' => 'liability', 'subtype' => 'tax', 'parent' => '2100'],
            ['code' => '2102', 'name' => 'AIT Payable', 'type' => 'liability', 'subtype' => 'tax', 'parent' => '2100'],
            ['code' => '2103', 'name' => 'Income Tax Payable', 'type' => 'liability', 'subtype' => 'tax', 'parent' => '2100'],
            ['code' => '2200', 'name' => 'Customer Deposits/Advances', 'type' => 'liability', 'subtype' => 'current_liability', 'parent' => null],
            ['code' => '2201', 'name' => 'Customer Wallet Balance', 'type' => 'liability', 'subtype' => 'current_liability', 'parent' => '2200'],
            ['code' => '2202', 'name' => 'Gift Card Balance', 'type' => 'liability', 'subtype' => 'current_liability', 'parent' => '2200'],
            ['code' => '2300', 'name' => 'Short-term Loans', 'type' => 'liability', 'subtype' => 'loan', 'parent' => null],
            ['code' => '2400', 'name' => 'Long-term Loans', 'type' => 'liability', 'subtype' => 'loan', 'parent' => null],

            ['code' => '3000', 'name' => "Owner's Equity", 'type' => 'equity', 'subtype' => null, 'parent' => null],
            ['code' => '3001', 'name' => "Owner's Capital", 'type' => 'equity', 'subtype' => null, 'parent' => '3000'],
            ['code' => '3002', 'name' => "Owner's Drawings", 'type' => 'equity', 'subtype' => null, 'parent' => '3000'],
            ['code' => '3100', 'name' => 'Retained Earnings', 'type' => 'equity', 'subtype' => null, 'parent' => null],

            ['code' => '4000', 'name' => 'Sales Revenue', 'type' => 'income', 'subtype' => null, 'parent' => null],
            ['code' => '4001', 'name' => 'Product Sales', 'type' => 'income', 'subtype' => null, 'parent' => '4000'],
            ['code' => '4002', 'name' => 'Shipping Revenue', 'type' => 'income', 'subtype' => null, 'parent' => '4000'],
            ['code' => '4003', 'name' => 'Digital Product Sales', 'type' => 'income', 'subtype' => null, 'parent' => '4000'],
            ['code' => '4100', 'name' => 'Platform Income', 'type' => 'income', 'subtype' => null, 'parent' => null],
            ['code' => '4101', 'name' => 'Seller Commission', 'type' => 'income', 'subtype' => null, 'parent' => '4100'],
            ['code' => '4102', 'name' => 'Listing Fees', 'type' => 'income', 'subtype' => null, 'parent' => '4100'],
            ['code' => '4103', 'name' => 'Subscription Fees', 'type' => 'income', 'subtype' => null, 'parent' => '4100'],
            ['code' => '4200', 'name' => 'Other Income', 'type' => 'income', 'subtype' => null, 'parent' => null],
            ['code' => '4201', 'name' => 'Interest Income', 'type' => 'income', 'subtype' => null, 'parent' => '4200'],
            ['code' => '4202', 'name' => 'Miscellaneous Income', 'type' => 'income', 'subtype' => null, 'parent' => '4200'],

            ['code' => '5000', 'name' => 'Cost of Goods Sold', 'type' => 'cost_of_goods', 'subtype' => null, 'parent' => null],
            ['code' => '5001', 'name' => 'Product Cost', 'type' => 'cost_of_goods', 'subtype' => null, 'parent' => '5000'],
            ['code' => '5002', 'name' => 'Shipping Cost Paid', 'type' => 'cost_of_goods', 'subtype' => null, 'parent' => '5000'],
            ['code' => '5003', 'name' => 'Courier Charges', 'type' => 'cost_of_goods', 'subtype' => null, 'parent' => '5000'],
            ['code' => '5004', 'name' => 'Packaging Cost', 'type' => 'cost_of_goods', 'subtype' => null, 'parent' => '5000'],
            ['code' => '5005', 'name' => 'Seller Payouts', 'type' => 'cost_of_goods', 'subtype' => null, 'parent' => '5000'],

            ['code' => '6000', 'name' => 'Operating Expenses', 'type' => 'expense', 'subtype' => null, 'parent' => null],
            ['code' => '6001', 'name' => 'Salaries & Wages', 'type' => 'expense', 'subtype' => null, 'parent' => '6000'],
            ['code' => '6002', 'name' => 'Office Rent', 'type' => 'expense', 'subtype' => null, 'parent' => '6000'],
            ['code' => '6003', 'name' => 'Utilities (Electricity/Internet)', 'type' => 'expense', 'subtype' => null, 'parent' => '6000'],
            ['code' => '6004', 'name' => 'Office Supplies', 'type' => 'expense', 'subtype' => null, 'parent' => '6000'],
            ['code' => '6005', 'name' => 'Repairs & Maintenance', 'type' => 'expense', 'subtype' => null, 'parent' => '6000'],
            ['code' => '6100', 'name' => 'Marketing & Advertising', 'type' => 'expense', 'subtype' => null, 'parent' => null],
            ['code' => '6101', 'name' => 'Facebook/Google Ads', 'type' => 'expense', 'subtype' => null, 'parent' => '6100'],
            ['code' => '6102', 'name' => 'SMS Marketing', 'type' => 'expense', 'subtype' => null, 'parent' => '6100'],
            ['code' => '6103', 'name' => 'Promotional Discounts', 'type' => 'expense', 'subtype' => null, 'parent' => '6100'],
            ['code' => '6104', 'name' => 'Influencer/Affiliate Costs', 'type' => 'expense', 'subtype' => null, 'parent' => '6100'],
            ['code' => '6200', 'name' => 'Technology', 'type' => 'expense', 'subtype' => null, 'parent' => null],
            ['code' => '6201', 'name' => 'Software Subscriptions', 'type' => 'expense', 'subtype' => null, 'parent' => '6200'],
            ['code' => '6202', 'name' => 'Hosting & Server', 'type' => 'expense', 'subtype' => null, 'parent' => '6200'],
            ['code' => '6203', 'name' => 'Domain Names', 'type' => 'expense', 'subtype' => null, 'parent' => '6200'],
            ['code' => '6204', 'name' => 'Payment Gateway Fees', 'type' => 'expense', 'subtype' => null, 'parent' => '6200'],
            ['code' => '6300', 'name' => 'Financial', 'type' => 'expense', 'subtype' => null, 'parent' => null],
            ['code' => '6301', 'name' => 'Bank Charges', 'type' => 'expense', 'subtype' => null, 'parent' => '6300'],
            ['code' => '6302', 'name' => 'Payment Processing Fees', 'type' => 'expense', 'subtype' => null, 'parent' => '6300'],
            ['code' => '6303', 'name' => 'Interest Expense', 'type' => 'expense', 'subtype' => null, 'parent' => '6300'],
            ['code' => '6400', 'name' => 'Logistics', 'type' => 'expense', 'subtype' => null, 'parent' => null],
            ['code' => '6401', 'name' => 'Courier Service Fees', 'type' => 'expense', 'subtype' => null, 'parent' => '6400'],
            ['code' => '6402', 'name' => 'Warehouse Rent', 'type' => 'expense', 'subtype' => null, 'parent' => '6400'],
            ['code' => '6403', 'name' => 'Vehicle Fuel', 'type' => 'expense', 'subtype' => null, 'parent' => '6400'],
            ['code' => '6500', 'name' => 'Professional Services', 'type' => 'expense', 'subtype' => null, 'parent' => null],
            ['code' => '6501', 'name' => 'Accounting/Audit', 'type' => 'expense', 'subtype' => null, 'parent' => '6500'],
            ['code' => '6502', 'name' => 'Legal Fees', 'type' => 'expense', 'subtype' => null, 'parent' => '6500'],
            ['code' => '6503', 'name' => 'Consultancy', 'type' => 'expense', 'subtype' => null, 'parent' => '6500'],
            ['code' => '6600', 'name' => 'Depreciation', 'type' => 'expense', 'subtype' => null, 'parent' => null],
            ['code' => '6601', 'name' => 'Equipment Depreciation', 'type' => 'expense', 'subtype' => null, 'parent' => '6600'],
        ];

        foreach ($data as $row) {
            $parentId = null;
            if (! empty($row['parent'])) {
                $parentId = Account::query()->where('code', $row['parent'])->value('id');
            }
            Account::query()->updateOrCreate(
                ['code' => $row['code']],
                [
                    'name' => $row['name'],
                    'type' => $row['type'],
                    'subtype' => $row['subtype'],
                    'parent_id' => $parentId,
                    'is_system' => true,
                    'is_active' => true,
                ]
            );
        }
    }
}

