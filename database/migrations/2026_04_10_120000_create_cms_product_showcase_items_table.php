<?php

use App\Models\CmsProduct;
use App\Models\CmsProductShowcaseItem;
use App\Models\Product;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('cms_product_showcase_items')) {
            Schema::create('cms_product_showcase_items', function (Blueprint $table) {
                $table->id();
                $table->foreignId('cms_product_id')->constrained('cms_products')->cascadeOnDelete();
                $table->foreignId('product_id')->nullable()->constrained('products')->nullOnDelete();
                $table->integer('sort_order')->default(0);
                $table->boolean('is_active')->default(1);
                $table->string('card_type')->nullable();
                $table->string('title')->nullable();
                $table->text('description')->nullable();
                $table->string('image')->nullable();
                $table->string('primary_button_text')->nullable();
                $table->string('primary_button_link')->nullable();
                $table->timestamps();

                $table->unique(['cms_product_id', 'product_id'], 'cms_product_showcase_unique');
            });
        }

        $featureSection = CmsProduct::query()->where('type', 'feature_product')->first();
        if (!$featureSection) {
            return;
        }

        $selectedProductIds = collect($featureSection->selected_item_ids ?? [])
            ->filter(fn ($id) => is_numeric($id))
            ->map(fn ($id) => (int) $id)
            ->values();

        if ($selectedProductIds->isEmpty()) {
            return;
        }

        $products = Product::query()
            ->whereIn('id', $selectedProductIds)
            ->get()
            ->keyBy('id');

        foreach ($selectedProductIds as $index => $productId) {
            $product = $products->get($productId);

            if (!$product) {
                continue;
            }

            $description = trim(strip_tags((string) ($product->details ?? '')));

            CmsProductShowcaseItem::query()->firstOrCreate(
                [
                    'cms_product_id' => $featureSection->id,
                    'product_id' => $product->id,
                ],
                [
                    'sort_order' => $index + 1,
                    'is_active' => 1,
                    'card_type' => 'product',
                    'title' => $product->name,
                    'description' => $description !== '' ? Str::limit($description, 320) : $product->name,
                    'image' => null,
                    'primary_button_text' => 'View product',
                    'primary_button_link' => null,
                ]
            );
        }

        $featureSection->selected_item_ids = null;
        $featureSection->save();
    }

    public function down(): void
    {
        Schema::dropIfExists('cms_product_showcase_items');
    }
};
