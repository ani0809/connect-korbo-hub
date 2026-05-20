<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingsSeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            ['key'=>'site_name','value'=>'Cibato Commerce','type'=>'string','group'=>'general'],
            ['key'=>'site_logo','value'=>'','type'=>'file','group'=>'general'],
            ['key'=>'favicon','value'=>'','type'=>'file','group'=>'general'],
            ['key'=>'site_description','value'=>'Modern self-hosted eCommerce platform.','type'=>'string','group'=>'general'],
            ['key'=>'contact_email','value'=>'support@example.com','type'=>'string','group'=>'general'],
            ['key'=>'contact_phone','value'=>'+1-000-000-0000','type'=>'string','group'=>'general'],
            ['key'=>'address','value'=>'Business address','type'=>'string','group'=>'general'],
            ['key'=>'timezone','value'=>'UTC','type'=>'string','group'=>'general'],
            ['key'=>'currency','value'=>'USD','type'=>'string','group'=>'shop'],
            ['key'=>'currency_symbol','value'=>'$','type'=>'string','group'=>'shop'],
            ['key'=>'products_per_page','value'=>'20','type'=>'integer','group'=>'shop'],
            ['key'=>'low_stock_threshold','value'=>'5','type'=>'integer','group'=>'shop'],
            ['key'=>'enable_reviews','value'=>'1','type'=>'boolean','group'=>'shop'],
            ['key'=>'enable_wishlist','value'=>'1','type'=>'boolean','group'=>'shop'],
            ['key'=>'enable_compare','value'=>'0','type'=>'boolean','group'=>'shop'],
            ['key'=>'tax_rate','value'=>'0','type'=>'float','group'=>'shop'],
            ['key'=>'active_header_preset','value'=>'preset-1','type'=>'string','group'=>'builder'],
            ['key'=>'active_footer_preset','value'=>'preset-1','type'=>'string','group'=>'builder'],
            ['key'=>'active_product_card','value'=>'card-1','type'=>'string','group'=>'builder'],
            ['key'=>'header_config_json','value'=>json_encode([]),'type'=>'json','group'=>'builder'],
            ['key'=>'footer_config_json','value'=>json_encode([]),'type'=>'json','group'=>'builder'],
            ['key'=>'homepage_sections_json','value'=>json_encode([]),'type'=>'json','group'=>'builder'],
            ['key'=>'primary_color','value'=>'#2563eb','type'=>'string','group'=>'builder'],
            ['key'=>'secondary_color','value'=>'#1e293b','type'=>'string','group'=>'builder'],
            ['key'=>'accent_color','value'=>'#f59e0b','type'=>'string','group'=>'builder'],
            ['key'=>'body_font','value'=>'Inter','type'=>'string','group'=>'builder'],
            ['key'=>'heading_font','value'=>'Inter','type'=>'string','group'=>'builder'],
            ['key'=>'facebook','value'=>'','type'=>'string','group'=>'social'],
            ['key'=>'instagram','value'=>'','type'=>'string','group'=>'social'],
            ['key'=>'twitter','value'=>'','type'=>'string','group'=>'social'],
            ['key'=>'youtube','value'=>'','type'=>'string','group'=>'social'],
            ['key'=>'whatsapp','value'=>'','type'=>'string','group'=>'social'],
            ['key'=>'active_gateways','value'=>json_encode(['cod']),'type'=>'json','group'=>'payment'],
            ['key'=>'active_method','value'=>'flat_rate','type'=>'string','group'=>'shipping'],
            ['key'=>'free_shipping_minimum','value'=>'0','type'=>'float','group'=>'shipping'],
        ];

        foreach ($rows as $row) {
            Setting::query()->updateOrCreate(['key' => $row['key']], array_merge($row, ['autoload' => true]));
        }
    }
}
