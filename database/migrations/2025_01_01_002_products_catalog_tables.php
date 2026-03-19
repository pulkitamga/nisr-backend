<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * PRODUCTS: Product catalog and related tables
 *
 * Creates all tables related to product catalog, categories,
 * brands, attributes, variations, and digital products.
 */
return new class extends Migration
{
    public function up(): void
    {
        // Products
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->nullable();
            $table->foreignId('brand_id')->nullable()->constrained('brands')->onDelete('set null');
            $table->foreignId('unit_id')->nullable();
            $table->foreignId('category_id')->nullable()->constrained('categories')->onDelete('set null');
            $table->foreignId('sub_category_id')->nullable()->constrained('categories')->onDelete('set null');
            $table->foreignId('sub_sub_category_id')->nullable()->constrained('categories')->onDelete('set null');
            $table->text('description')->nullable();
            $table->text('short_description')->nullable();
            $table->json('images')->nullable();
            $table->string('thumbnail')->nullable();
            $table->string('video')->nullable();
            $table->string('video_provider')->nullable();
            $table->string('video_link')->nullable();
            $table->string('unit')->nullable();
            $table->boolean('is_feature')->default(false);
            $table->boolean('is_choice')->default(false);
            $table->boolean('is_popular')->default(false);
            $table->boolean('is_new')->default(false);
            $table->boolean('is_top')->default(false);
            $table->boolean('is_best')->default(false);
            $table->boolean('is_warranty')->default(false);
            $table->boolean('is_traceable')->default(false);
            $table->integer('warranty_duration')->nullable();
            $table->boolean('is_digital')->default(false);
            $table->boolean('is_grouped')->default(false);
            $table->boolean('is_organic')->default(false);
            $table->boolean('is_physical')->default(true);
            $table->boolean('is_kyc_required')->default(false);
            $table->boolean('is_guest_checkout')->default(true);
            $table->boolean('status')->default(false);
            $table->integer('minimum_order_qty')->default(1);
            $table->integer('maximum_order_qty')->nullable();
            $table->decimal('tax', 24, 2)->default(0);
            $table->foreignId('tax_id')->nullable();
            $table->string('tax_type')->default('percent');
            $table->boolean('is_shipping_cost_included')->default(false);
            $table->boolean('is_est_shipping_time')->default(true);
            $table->integer('est_shipping_days')->nullable();
            $table->boolean('multiply_attributes')->default(false);
            $table->integer('order_count')->default(0);
            $table->integer('review_count')->default(0);
            $table->integer('rating_count')->default(0);
            $table->enum('rating', ['1', '2', '3', '4', '5'])->nullable();
            $table->integer('wishlist_count')->default(0);
            $table->string('meta_title')->nullable();
            $table->string('meta_description')->nullable();
            $table->integer('seller_id')->nullable();
            $table->foreignId('shop_id')->nullable()->constrained('shops')->onDelete('cascade');
            $table->string('added_by')->default('admin');
            $table->boolean('is_approved')->default(true);
            $table->enum('status_color', ['green', 'yellow', 'red'])->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // Categories
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->nullable();
            $table->foreignId('parent_id')->nullable()->constrained('categories')->onDelete('cascade');
            $table->integer('position')->default(0);
            $table->string('icon')->nullable();
            $table->string('image')->nullable();
            $table->string('banner')->nullable();
            $table->text('description')->nullable();
            $table->integer('priority')->default(0);
            $table->boolean('status')->default(true);
            $table->timestamps();
        });

        // Brands
        Schema::create('brands', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('image')->nullable();
            $table->text('description')->nullable();
            $table->string('meta_title')->nullable();
            $table->string('meta_description')->nullable();
            $table->boolean('status')->default(true);
            $table->timestamps();
        });

        // Attributes (for product variations)
        Schema::create('attributes', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('category_id')->nullable()->constrained('categories')->onDelete('cascade');
            $table->text('values')->nullable();
            $table->integer('position')->default(0);
            $table->boolean('status')->default(true);
            $table->timestamps();
        });

        // Colors
        Schema::create('colors', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code');
            $table->timestamps();
        });

        // Product Stocks (variations)
        Schema::create('product_stocks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->string('variant')->nullable();
            $table->string('sku')->nullable();
            $table->integer('qty')->default(0);
            $table->decimal('price', 24, 2)->default(0);
            $table->string('image')->nullable();
            $table->timestamps();
        });

        // Product Stock Transactions
        Schema::create('product_stock_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->foreignId('product_stock_id')->nullable()->constrained('product_stocks')->onDelete('set null');
            $table->string('type'); // in, out, transfer, adjustment
            $table->integer('quantity')->default(0);
            $table->foreignId('branch_id')->nullable()->constrained('branches')->onDelete('set null');
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('admins')->onDelete('set null');
            $table->timestamps();
        });

        // Tags
        Schema::create('tags', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->nullable();
            $table->integer('priority')->default(0);
            $table->timestamps();
        });

        // Product Tag (pivot)
        Schema::create('product_tag', function (Blueprint $table) {
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->foreignId('tag_id')->constrained('tags')->onDelete('cascade');
            $table->primary(['product_id', 'tag_id']);
        });

        // Product SEO
        Schema::create('product_seos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->string('meta_keywords')->nullable();
            $table->timestamps();
        });

        // Product Compare
        Schema::create('product_compares', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('cascade');
            $table->string('guest_id')->nullable();
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->timestamps();
        });

        // Wishlists
        Schema::create('wishlists', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('cascade');
            $table->string('guest_id')->nullable();
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->timestamps();
        });

        // Reviews
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('cascade');
            $table->integer('rating');
            $table->text('comment')->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->boolean('is_anonymous')->default(false);
            $table->integer('helpful_count')->default(0);
            $table->timestamps();
        });

        // Review Replies
        Schema::create('review_replies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('review_id')->constrained('reviews')->onDelete('cascade');
            $table->foreignId('admin_id')->nullable()->constrained('admins')->onDelete('set null');
            $table->text('reply');
            $table->timestamps();
        });

        // Digital Products
        Schema::create('digital_product_authors', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });

        Schema::create('digital_product_publishing_houses', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });

        Schema::create('digital_product_variations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->string('variant_key')->nullable();
            $table->text('variant_value')->nullable();
            $table->string('file')->nullable();
            $table->timestamps();
        });

        // Publishing Houses (legacy)
        Schema::create('publishing_houses', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });

        // Authors (legacy)
        Schema::create('authors', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });

        // Digital Product OTP Verification
        Schema::create('digital_product_otp_verifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('cascade');
            $table->string('otp');
            $table->timestamp('expires_at');
            $table->boolean('is_verified')->default(false);
            $table->timestamps();
        });

        // Featured/Most Demanded Products
        Schema::create('most_demandeds', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->integer('priority')->default(0);
            $table->boolean('status')->default(true);
            $table->timestamps();
        });

        // Deal of the Day
        Schema::create('deal_of_the_days', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->date('start_date');
            $table->date('end_date');
            $table->decimal('discount', 24, 2)->default(0);
            $table->string('discount_type')->default('percent');
            $table->boolean('status')->default(true);
            $table->timestamps();
        });

        // Feature Deals
        Schema::create('feature_deals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->date('start_date');
            $table->date('end_date');
            $table->decimal('discount', 24, 2)->default(0);
            $table->string('discount_type')->default('percent');
            $table->boolean('status')->default(true);
            $table->timestamps();
        });

        // Flash Deals
        Schema::create('flash_deals', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->date('start_date');
            $table->date('end_date');
            $table->boolean('status')->default(true);
            $table->integer('background_color')->default(0);
            $table->integer('text_color')->default(0);
            $table->string('slug')->nullable();
            $table->timestamps();
        });

        Schema::create('flash_deal_products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('flash_deal_id')->constrained('flash_deals')->onDelete('cascade');
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->decimal('discount', 24, 2)->default(0);
            $table->string('discount_type')->default('percent');
            $table->integer('quantity')->default(0);
            $table->integer('sold_count')->default(0);
            $table->timestamps();
        });

        // Restock Products
        Schema::create('restock_products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->integer('quantity')->default(0);
            $table->date('expected_date')->nullable();
            $table->timestamps();
        });

        Schema::create('restock_product_customers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('restock_product_id')->constrained('restock_products')->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('cascade');
            $table->string('guest_id')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('restock_product_customers');
        Schema::dropIfExists('restock_products');
        Schema::dropIfExists('flash_deal_products');
        Schema::dropIfExists('flash_deals');
        Schema::dropIfExists('feature_deals');
        Schema::dropIfExists('deal_of_the_days');
        Schema::dropIfExists('most_demandeds');
        Schema::dropIfExists('digital_product_otp_verifications');
        Schema::dropIfExists('authors');
        Schema::dropIfExists('publishing_houses');
        Schema::dropIfExists('digital_product_variations');
        Schema::dropIfExists('digital_product_publishing_houses');
        Schema::dropIfExists('digital_product_authors');
        Schema::dropIfExists('review_replies');
        Schema::dropIfExists('reviews');
        Schema::dropIfExists('wishlists');
        Schema::dropIfExists('product_compares');
        Schema::dropIfExists('product_seos');
        Schema::dropIfExists('product_tag');
        Schema::dropIfExists('tags');
        Schema::dropIfExists('product_stock_transactions');
        Schema::dropIfExists('product_stocks');
        Schema::dropIfExists('colors');
        Schema::dropIfExists('attributes');
        Schema::dropIfExists('brands');
        Schema::dropIfExists('categories');
        Schema::dropIfExists('products');
    }
};
