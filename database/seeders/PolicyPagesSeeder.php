<?php

namespace Database\Seeders;

use App\Models\StaticPage;
use Illuminate\Database\Seeder;

class PolicyPagesSeeder extends Seeder
{
    public function run(): void
    {
        $pages = [
            [
                'slug' => 'privacy-policy',
                'title' => 'Privacy Policy',
                'template' => 'default',
                'content' => '<p><strong>PLACEHOLDER.</strong> Replace this text with your real privacy policy. This page was auto-generated for setup.</p><p>We describe how we collect, use, and protect personal data.</p>',
            ],
            [
                'slug' => 'terms-of-service',
                'title' => 'Terms of Service',
                'template' => 'default',
                'content' => '<p><strong>PLACEHOLDER.</strong> Replace with your store terms of service, including limitations of liability and governing law.</p>',
            ],
            [
                'slug' => 'return-policy',
                'title' => 'Return Policy',
                'template' => 'default',
                'content' => '<p><strong>PLACEHOLDER.</strong> Describe return windows, restocking fees, and how customers start a return.</p>',
            ],
            [
                'slug' => 'shipping-policy',
                'title' => 'Shipping Policy',
                'template' => 'default',
                'content' => '<p><strong>PLACEHOLDER.</strong> Describe carriers, processing times, international shipping, and tracking.</p>',
            ],
            [
                'slug' => 'about-us',
                'title' => 'About Us',
                'template' => 'blank',
                'content' => '<section style="max-width:720px;margin:4rem auto;padding:2rem;font-family:sans-serif"><h1>About our store</h1><p><strong>PLACEHOLDER.</strong> Tell your brand story here.</p></section>',
            ],
        ];

        foreach ($pages as $row) {
            StaticPage::query()->updateOrCreate(
                ['slug' => $row['slug']],
                array_merge($row, [
                    'is_active' => true,
                    'show_in_sitemap' => true,
                    'sort_order' => 0,
                ])
            );
        }
    }
}
