<?php

use App\Models\CmsService;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        if (!class_exists(CmsService::class)) {
            return;
        }

        $sections = [
            'main_banner',
            'hero_slider',
            'service_showcase',
        ];

        foreach ($sections as $type) {
            CmsService::query()->firstOrCreate(
                ['type' => $type],
                [
                    'heading' => null,
                    'description' => null,
                    'button_text' => null,
                    'button_link' => null,
                    'image' => null,
                    'selected_item_ids' => null,
                    'is_active' => 1,
                ]
            );
        }
    }

    public function down(): void
    {
        CmsService::query()->whereIn('type', ['main_banner', 'hero_slider', 'service_showcase'])->delete();
    }
};
