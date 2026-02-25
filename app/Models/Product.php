<?php

namespace App\Models;

use App\Traits\CacheManagerTrait;
use App\Traits\StorageTrait;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\SoftDeletes;


/*
    Reason : for branch_id
*/

/**
 * @property int $user_id
 * @property string $added_by
 * @property string $name
 * @property string $code
 * @property string $slug
 * @property int $branch_id
 * @property int $category_id
 * @property int $sub_category_id
 * @property int $sub_sub_category_id
 * @property int $brand_id
 * @property string $unit
 * @property string $digital_product_type
 * @property string $product_type
 * @property string $details
 * @property int $min_qty
 * @property int $published
 * @property float $tax
 * @property string $tax_type
 * @property string $tax_model
 * @property float $unit_price
 * @property int $status
 * @property float $discount
 * @property int $current_stock
 * @property int $minimum_order_qty
 * @property int $free_shipping
 * @property int $request_status
 * @property int $featured_status
 * @property int $refundable
 * @property int $featured
 * @property int $flash_deal
 * @property int $seller_id
 * @property float $purchase_price
 * @property float $shipping_cost
 * @property int $multiply_qty
 * @property float $temp_shipping_cost
 * @property string $thumbnail
 * @property string $thumbnail_storage_type
 * @property string $preview_file
 * @property string $preview_file_storage_type
 * @property string $digital_file_ready
 * @property string $meta_title
 * @property string $meta_description
 * @property string $meta_image
 * @property int $is_shipping_cost_updated
 */
class Product extends Model
{
    use StorageTrait, CacheManagerTrait, SoftDeletes;

    protected $fillable = [
        'user_id',
        'added_by',
        'name',
        'code',
        'slug',
        'category_ids',
        'branch_id',
        'category_id',
        'sub_category_id',
        'sub_sub_category_id',
        'brand_id',
        'unit',
        'digital_product_type',
        'product_type',
        'details',
        'colors',
        'choice_options',
        'variation',
        'digital_product_file_types',
        'digital_product_extensions',
        'unit_price',
        'purchase_price',
        'tax',
        'tax_type',
        'tax_model',
        'discount',
        'discount_type',
        'attributes',
        'current_stock',
        'minimum_order_qty',
        'video_provider',
        'video_url',
        'status',
        'featured_status',
        'show_cms',
        'showcase_product',
        'featured',
        'request_status',
        'shipping_cost',
        'multiply_qty',
        'color_image',
        'images',
        'thumbnail',
        'thumbnail_storage_type',
        'preview_file',
        'preview_file_storage_type',
        'digital_file_ready',
        'meta_title',
        'meta_description',
        'meta_image',
        'thumbnail_storage_type',
        'digital_file_ready_storage_type',
        'is_shipping_cost_updated',
        'temp_shipping_cost',
        'match_makes',
        'match_models',
        'match_years',
        'is_warranty',
    ];

    /**
     * @var string[]
     */
    protected $casts = [
        'user_id' => 'integer',
        'added_by' => 'string',
        'name' => 'string',
        'code' => 'string',
        'slug' => 'string',
        'branch_id' => 'integer',
        'category_id' => 'integer',
        'sub_category_id' => 'integer',
        'sub_sub_category_id' => 'integer',
        'brand_id' => 'integer',
        'unit' => 'string',
        'digital_product_type' => 'string',
        'product_type' => 'string',
        'details' => 'string',
        'min_qty' => 'integer',
        'published' => 'integer',
        'tax' => 'float',
        'tax_type' => 'string',
        'tax_model' => 'string',
        'unit_price' => 'float',
        'status' => 'integer',
        'discount' => 'float',
        'current_stock' => 'integer',
        'minimum_order_qty' => 'integer',
        'free_shipping' => 'integer',
        'request_status' => 'integer',
        'featured_status' => 'integer',
        'refundable' => 'integer',
        'featured' => 'integer',
        'flash_deal' => 'integer',
        'seller_id' => 'integer',
        'purchase_price' => 'float',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'shipping_cost' => 'float',
        'multiply_qty' => 'integer',
        'temp_shipping_cost' => 'float',
        'thumbnail' => 'string',
        'preview_file' => 'string',
        'digital_file_ready' => 'string',
        'attributes' => 'array',
        'meta_title' => 'string',
        'meta_description' => 'string',
        'meta_image' => 'string',
        'is_shipping_cost_updated' => 'integer',
        'digital_product_file_types' => 'array',
        'digital_product_extensions' => 'array',
        'thumbnail_storage_type' => 'string',
        'digital_file_ready_storage_type' => 'string',
        'match_makes' => 'array',
        'match_models' => 'array',
        'match_years' => 'array',
        'is_warranty' => 'boolean',
    ];


    protected $dates = ['deleted_at'];

    protected $appends = ['is_shop_temporary_close', 'thumbnail_full_url', 'preview_file_full_url', 'color_images_full_url', 'meta_image_full_url', 'images_full_url', 'digital_file_ready_full_url'];

    public function translations(): MorphMany
    {
        return $this->morphMany('App\Models\Translation', 'translationable');
    }

    public function scopeActive($query)
    {
        $brandSetting = getWebConfig(name: 'product_brand');
        $digitalProductSetting = getWebConfig(name: 'digital_product');
        $businessMode = getWebConfig(name: 'business_mode');
        $productType = $digitalProductSetting ? ['digital', 'physical', 'services'] : ['physical', 'services'];

        return $query->when($businessMode == 'single', function ($query) {
            $query->where(['added_by' => 'admin']);
        })
            ->when($brandSetting, function ($query) use ($productType) {
                if (!in_array('digital', $productType)) {
                    $query->whereHas('brand', function ($query) {
                        $query->where('status', 1);
                    });
                }
            })
            // don't apply brand_id filter when brandSetting is off
            ->where(['status' => 1])
            ->where(['request_status' => 1])
            ->SellerApproved()
            ->whereIn('product_type', $productType);
    }


    public function scopeSellerApproved($query): void
    {
        $query->whereHas('seller', function ($query) {
            $query->where(['status' => 'approved']);
        })->orWhere(function ($query) {
            $query->where(['added_by' => 'admin', 'status' => 1]);
        });
    }

    public function stocks(): HasMany
    {
        return $this->hasMany(ProductStock::class);
    }


    // App\Models\Product.php

    public function wholesaleProducts()
    {
        return $this->hasMany(\App\Models\WholeSaleProducts::class, 'product_id', 'id');
    }


    public function getWholesalePrice()
    {
        if (!auth('customer')->check()) {
            return null;
        }

        $user = auth('customer')->user();

        if ($user->wholesaler_status != 1) {
            return null;
        }

        if (!$this->wholesale_discount_percent) {
            return null;
        }

        $percent = $this->wholesale_discount_percent;
        $unitPrice = $this->unit_price;

        $discountAmount = ($unitPrice * $percent) / 100;
        $finalPrice = $unitPrice - $discountAmount;

        return [
            'original_price' => $unitPrice,
            'discount_percent' => $percent,
            'discount_amount' => $discountAmount,
            'final_price' => $finalPrice,
        ];
    }


    public function service()
    {
        return $this->hasOne(Service::class); // assuming 1-to-1 relation
    }
    public function clearanceSale(): HasOne
    {
        return $this->hasOne(StockClearanceProduct::class, 'product_id');
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class, 'product_id');
    }

    //old relation: reviews_by_customer
    public function reviewsByCustomer(): HasMany
    {
        return $this->hasMany(Review::class, 'product_id')->where('customer_id', auth('customer')->id())->whereNotNull('product_id')->whereNull('delivery_man_id');
    }

    public function digitalProductAuthors(): HasMany
    {
        return $this->hasMany(DigitalProductAuthor::class, 'product_id');
    }

    public function digitalProductPublishingHouse(): HasMany
    {
        return $this->hasMany(DigitalProductPublishingHouse::class, 'product_id');
    }

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    public function scopeStatus($query): Builder
    {
        return $query->where('featured_status', 1);
    }

    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class, 'seller_id');
    }

    public function seller(): BelongsTo
    {
        return $this->belongsTo(Seller::class, 'user_id');
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function getVariationPrice($type, $key = null)
    {
        $variations = json_decode($this->variation, true) ?? [];

        foreach ($variations as $v) {
            if ($v['type'] == $type ) {
                return $v['price'] ?? $this->unit_price;
            }
        }
        return $this->unit_price;
    }
   
    public function getVariationsAttribute()
{
    $variation = $this->variation;
 
    // null / empty
    if (empty($variation)) {
        return collect();
    }
 
    // JSON string → array
    if (is_string($variation)) {
        $variation = json_decode($variation, true);
    }
 
    // safety
    if (!is_array($variation)) {
        return collect();
    }
 
    return collect($variation);
}

    public function getIsShopTemporaryCloseAttribute($value): int
    {
        $inHouseTemporaryClose = Cache::get(IN_HOUSE_SHOP_TEMPORARY_CLOSE_STATUS) ?? 0;
        if ($this->added_by == 'admin') {
            return $inHouseTemporaryClose ?? 0;
        } elseif ($this->added_by == 'seller') {
            return Cache::remember('product-shop-close-' . $this->id, 3600, function () {
                return $this?->seller?->shop?->temporary_close ?? 0;
            });
        }
        return 0;
    }
    // Product.php mein — ye method daal do (purana sab hata do)
    // Product.php
    public function getProductAttributesAttribute()
    {
        $attrRaw = $this->attributes; // DB column

        if (empty($attrRaw)) {
            return collect();
        }

        $decoded = $attrRaw;

        // Agar string hai to decode kar
        if (is_string($decoded)) {
            $decoded = json_decode($decoded, true);

            // Agar decode karne ke baad phir bhi string ya array with string hai, dubara decode karo
            if (is_string($decoded)) {
                $decoded = json_decode($decoded, true);
            }
        }

        if (empty($decoded)) {
            return collect();
        }

        // Agar ab bhi numeric string ya number hai, array me daal do
        if (!is_array($decoded)) {
            $decoded = [$decoded];
        }

        // IDs safely nikal lo
        $ids = collect($decoded)->map(function ($item) {
            if (is_numeric($item)) return (int)$item;
            if (is_array($item) && isset($item['id'])) return (int)$item['id'];
            return null;
        })->filter()->unique()->values()->all();

        if (empty($ids)) {
            return collect();
        }

        // Attributes DB se le lo
        return \App\Models\Attribute::whereIn('id', $ids)
            ->select('id', 'name')
            ->get();
    }


    public function branches(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    //old relation: sub_category
    public function subCategory(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'sub_category_id');
    }

    //old relation: sub_sub_category
    public function subSubCategory(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'sub_sub_category_id');
    }

    public function rating(): HasMany
    {
        return $this->hasMany(Review::class)
            ->select(DB::raw('avg(rating) average, product_id'))
            ->whereNull('delivery_man_id')
            ->groupBy('product_id');
    }

    //old relation: order_details
    public function orderDetails(): HasMany
    {
        return $this->hasMany(OrderDetail::class, 'product_id');
    }

    public function seoInfo(): BelongsTo
    {
        return $this->belongsTo(ProductSeo::class, 'id', 'product_id');
    }

    //old relation: order_delivered
    public function orderDelivered(): HasMany
    {
        return $this->hasMany(OrderDetail::class, 'product_id')
            ->where('delivery_status', 'delivered');
    }

    //old relation: wish_list
    public function wishList(): HasMany
    {
        return $this->hasMany(Wishlist::class, 'product_id');
    }

    public function digitalVariation(): HasMany
    {
        return $this->hasMany(DigitalProductVariation::class, 'product_id');
    }

    public function tags(): BelongsToMany
    {
        if (strpos(url()->current(), '/api')) {
            return $this->belongsToMany(Tag::class)->limit(5);
        }
        return $this->belongsToMany(Tag::class);
    }

    //old relation: flash_deal_product
    public function flashDealProducts(): HasMany
    {
        return $this->hasMany(FlashDealProduct::class);
    }

    public function scopeFlashDeal($query, $flashDealID)
    {
        return $query->whereHas('flashDealProducts.flashDeal', function ($query) use ($flashDealID) {
            return $query->where('id', $flashDealID);
        });
    }

    //old relation: compare_list
    public function compareList(): HasMany
    {
        return $this->hasMany(ProductCompare::class);
    }

    public function getNameAttribute($name): string|null
    {
        if (strpos(url()->current(), '/admin') || strpos(url()->current(), '/vendor') || strpos(url()->current(), '/seller')) {
            return $name;
        }
        return $this->translations[0]->value ?? $name;
    }

    public function getDetailsAttribute($detail): string|null
    {
        if (strpos(url()->current(), '/admin') || strpos(url()->current(), '/vendor') || strpos(url()->current(), '/seller')) {
            return $detail;
        }
        return $this->translations[1]->value ?? $detail;
    }
    public function getThumbnailFullUrlAttribute(): string|null|array
    {
        $value = $this->thumbnail;
        return $this->storageLink('product/thumbnail', $value, $this->thumbnail_storage_type ?? 'public');
    }

    public function getPreviewFileFullUrlAttribute(): string|null|array
    {
        $value = $this->preview_file;
        return $this->storageLink('product/preview', $value, $this->preview_file_storage_type ?? 'public');
    }

    public function getMetaImageFullUrlAttribute(): array
    {
        $value = $this->meta_image;
        return $this->storageLink('product/meta', $value, 'public');
    }

    public function getDigitalFileReadyFullUrlAttribute(): array
    {
        $value = $this->digital_file_ready;
        return $this->storageLink('product/digital-product', $value, $this->digital_file_ready_storage_type ?? 'public');
    }

    public function getColorImagesFullUrlAttribute(): array
    {
        $images = [];
        $value = json_decode($this->color_image);
        if ($value) {
            foreach ($value as $item) {
                $item = (array)$item;
                $images[] = [
                    'color' => $item['color'],
                    'image_name' => $this->storageLink('product', $item['image_name'], $item['storage'] ?? 'public')
                ];
            }
        }
        return $images;
    }

    public function getImagesFullUrlAttribute(): array
    {
        $images = [];
        $value = is_array($this->images) ? $this->images : json_decode($this->images);

        if ($value) {
            foreach ($value as $item) {
                $item = isset($item->image_name) ? (array)$item : ['image_name' => $item, 'storage' => 'public'];
                $images[] = $this->storageLink('product', $item['image_name'], $item['storage'] ?? 'public');
            }
        }

        return $images;
    }


    protected static function boot(): void
    {
        parent::boot();

        static::saved(function ($model) {
            cacheRemoveByType(type: 'products');
        });

        static::deleted(function ($model) {
            cacheRemoveByType(type: 'products');
        });

        static::addGlobalScope('translate', function (Builder $builder) {
            $builder->with(['translations' => function ($query) {
                if (strpos(url()->current(), '/api')) {
                    return $query->where('locale', App::getLocale());
                } else {
                    return $query->where('locale', getDefaultLanguage());
                }
            }, 'reviews' => function ($query) {
                $query->whereNull('delivery_man_id');
            }]);
        });
    }

     public function warranties(): HasMany
    {
        return $this->hasMany(Warranty::class, 'product_id');
    }
}
