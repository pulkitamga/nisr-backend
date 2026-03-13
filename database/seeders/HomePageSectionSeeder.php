<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\HomePageSection;

class HomePageSectionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $sections = [
            ['type' => 'main_banner', 'name' => 'Main Banner'],
            ['type' => 'trusted_by', 'name' => 'Trusted By'],
            ['type' => 'deals', 'name' => 'Deals'],
            ['type' => 'products', 'name' => 'Products'],
            ['type' => 'why_choose_us', 'name' => 'Why Choose Us'],
            ['type' => 'why_join_us', 'name' => 'Why Join Us'],
            ['type' => 'blog', 'name' => 'Blog'],
            ['type' => 'client_review', 'name' => 'Client Review'],
            ['type' => 'wholesaler_section', 'name' => 'Wholesaler Section'],
            ['type' => 'find_perfect_match', 'name' => 'Find Perfect Match'],
            ['type' => 'faq', 'name' => 'FAQ Asked Questions'],
            ['type' => 'download_app', 'name' => 'Download Mobile App'],
        ];

        foreach ($sections as $section) {
            HomePageSection::updateOrCreate(
                ['type' => $section['type']],
                ['name' => $section['name']]
            );
        }
    }
}
