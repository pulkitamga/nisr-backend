<?php

namespace Tests\Feature;

use App\Contracts\Repositories\BusinessSettingRepositoryInterface;
use App\Contracts\Repositories\DealOfTheDayRepositoryInterface;
use App\Contracts\Repositories\FlashDealRepositoryInterface;
use App\Contracts\Repositories\ProductRepositoryInterface;
use App\Http\Controllers\Admin\Promotion\DealOfTheDayController;
use App\Http\Controllers\Admin\Promotion\FeaturedDealController;
use App\Http\Controllers\Admin\Promotion\FlashDealController;
use App\Models\BusinessSetting;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class DealListSearchValueSanitizationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::dropIfExists('business_settings');
        Schema::dropIfExists('translations');

        Schema::create('business_settings', function (Blueprint $table): void {
            $table->id();
            $table->string('type');
            $table->text('value')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('translations', function (Blueprint $table): void {
            $table->id();
            $table->string('translationable_type');
            $table->unsignedBigInteger('translationable_id');
            $table->string('locale');
            $table->string('key');
            $table->text('value')->nullable();
            $table->integer('item_index')->nullable();
            $table->timestamps();
        });

        DB::table('business_settings')->insert([
            [
                'type' => 'pagination_limit',
                'value' => '15',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    protected function tearDown(): void
    {
        Cache::flush();
        Schema::dropIfExists('business_settings');
        Schema::dropIfExists('translations');

        parent::tearDown();
    }

    public function test_featured_deal_list_normalizes_array_search_value(): void
    {
        $flashDealRepo = new class implements FlashDealRepositoryInterface {
            public mixed $capturedSearchValue = 'not-called';

            public function add(array $data): string|object { return new \stdClass(); }
            public function getFirstWhere(array $params, array $relations = []): ?\Illuminate\Database\Eloquent\Model { return null; }
            public function getList(array $orderBy = [], array $relations = [], int|string $dataLimit = DEFAULT_DATA_LIMIT, int $offset = null): EloquentCollection|LengthAwarePaginator { return new LengthAwarePaginator(collect(), 0, 15); }
            public function getListWhere(array $orderBy = [], string $searchValue = null, array $filters = [], array $relations = [], int|string $dataLimit = DEFAULT_DATA_LIMIT, int $offset = null): EloquentCollection|LengthAwarePaginator { return new LengthAwarePaginator(collect(), 0, 15); }
            public function update(string $id, array $data): bool { return true; }
            public function delete(array $params): bool { return true; }
            public function getFirstWhereWithoutGlobalScope(array $params, array $relations = []): ?\Illuminate\Database\Eloquent\Model { return null; }
            public function updateWhere(array $params, array $data): bool { return true; }
            public function getListWithRelations(array $orderBy = [], string $searchValue = null, array $filters = [], array $withCount = [], array $relations = [], int|string $dataLimit = DEFAULT_DATA_LIMIT, int $offset = null): EloquentCollection|LengthAwarePaginator
            {
                $this->capturedSearchValue = $searchValue;

                return new LengthAwarePaginator(collect(), 0, 15);
            }
        };

        $businessSettingRepo = $this->createMock(BusinessSettingRepositoryInterface::class);
        $businessSettingRepo->method('getFirstWhere')
            ->willReturn(new BusinessSetting(['value' => json_encode(['custom_sorting_status' => 0])]));

        $controller = new FeaturedDealController($flashDealRepo, $businessSettingRepo);
        $view = $controller->getListView(Request::create('/admin/deal/feature', 'GET', [
            'searchValue' => ['unsafe'],
        ]));

        $this->assertNull($flashDealRepo->capturedSearchValue);
        $this->assertNull($view->getData()['searchValue']);
    }

    public function test_flash_deal_list_normalizes_array_search_value(): void
    {
        $flashDealRepo = new class implements FlashDealRepositoryInterface {
            public mixed $capturedSearchValue = 'not-called';

            public function add(array $data): string|object { return new \stdClass(); }
            public function getFirstWhere(array $params, array $relations = []): ?\Illuminate\Database\Eloquent\Model { return null; }
            public function getList(array $orderBy = [], array $relations = [], int|string $dataLimit = DEFAULT_DATA_LIMIT, int $offset = null): EloquentCollection|LengthAwarePaginator { return new LengthAwarePaginator(collect(), 0, 15); }
            public function getListWhere(array $orderBy = [], string $searchValue = null, array $filters = [], array $relations = [], int|string $dataLimit = DEFAULT_DATA_LIMIT, int $offset = null): EloquentCollection|LengthAwarePaginator { return new LengthAwarePaginator(collect(), 0, 15); }
            public function update(string $id, array $data): bool { return true; }
            public function delete(array $params): bool { return true; }
            public function getFirstWhereWithoutGlobalScope(array $params, array $relations = []): ?\Illuminate\Database\Eloquent\Model { return null; }
            public function updateWhere(array $params, array $data): bool { return true; }
            public function getListWithRelations(array $orderBy = [], string $searchValue = null, array $filters = [], array $withCount = [], array $relations = [], int|string $dataLimit = DEFAULT_DATA_LIMIT, int $offset = null): EloquentCollection|LengthAwarePaginator
            {
                $this->capturedSearchValue = $searchValue;

                return new LengthAwarePaginator(collect(), 0, 15);
            }
        };

        $businessSettingRepo = $this->createMock(BusinessSettingRepositoryInterface::class);
        $businessSettingRepo->method('getFirstWhere')
            ->willReturn(new BusinessSetting(['value' => json_encode(['custom_sorting_status' => 0])]));

        $controller = new FlashDealController(
            $this->createMock(ProductRepositoryInterface::class),
            $this->createMock(\App\Contracts\Repositories\FlashDealProductRepositoryInterface::class),
            $flashDealRepo,
            $this->createMock(\App\Contracts\Repositories\TranslationRepositoryInterface::class),
            $businessSettingRepo,
        );

        $view = $controller->getListView(Request::create('/admin/deal/flash', 'GET', [
            'searchValue' => ['unsafe'],
        ]));

        $this->assertNull($flashDealRepo->capturedSearchValue);
        $this->assertNull($view->getData()['searchValue']);
    }

    public function test_deal_of_the_day_list_normalizes_array_search_value(): void
    {
        $dealRepo = new class implements DealOfTheDayRepositoryInterface {
            public mixed $capturedSearchValue = 'not-called';

            public function add(array $data): string|object { return new \stdClass(); }
            public function getFirstWhere(array $params, array $relations = []): ?\Illuminate\Database\Eloquent\Model { return null; }
            public function getList(array $orderBy = [], array $relations = [], int|string $dataLimit = DEFAULT_DATA_LIMIT, int $offset = null): EloquentCollection|LengthAwarePaginator { return new LengthAwarePaginator(collect(), 0, 15); }
            public function getListWhere(array $orderBy = [], string $searchValue = null, array $filters = [], array $relations = [], int|string $dataLimit = DEFAULT_DATA_LIMIT, int $offset = null): EloquentCollection|LengthAwarePaginator
            {
                $this->capturedSearchValue = $searchValue;

                return new LengthAwarePaginator(collect(), 0, 15);
            }
            public function update(string $id, array $data): bool { return true; }
            public function delete(array $params): bool { return true; }
            public function getFirstWhereWithoutGlobalScope(array $params, array $relations = []): ?\Illuminate\Database\Eloquent\Model { return null; }
            public function updateWhere(array $params, array $data): bool { return true; }
        };

        $productRepo = $this->createMock(ProductRepositoryInterface::class);
        $productRepo->method('getListWithScope')->willReturn(new EloquentCollection());

        $controller = new DealOfTheDayController(
            $productRepo,
            $this->createMock(\App\Contracts\Repositories\TranslationRepositoryInterface::class),
            $dealRepo,
        );

        $view = $controller->getListView(Request::create('/admin/deal/day', 'GET', [
            'searchValue' => ['unsafe'],
        ]));

        $this->assertNull($dealRepo->capturedSearchValue);
        $this->assertNull($view->getData()['searchValue']);
    }
}
