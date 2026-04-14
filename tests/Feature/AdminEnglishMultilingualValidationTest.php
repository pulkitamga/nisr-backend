<?php

namespace Tests\Feature;

use App\Http\Requests\Admin\BrandAddRequest;
use App\Http\Requests\Admin\BranchAddRequest;
use App\Http\Requests\Admin\CategoryAddRequest;
use App\Http\Requests\Admin\HelpTopicAddRequest;
use App\Http\Requests\Admin\ShippingMethodRequest;
use App\Http\Requests\Admin\SubCategoryAddRequest;
use App\Http\Requests\Admin\WholesalerRegistrationReasonRequest;
use App\Services\BranchService;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class AdminEnglishMultilingualValidationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::dropIfExists('business_settings');
        Schema::dropIfExists('brands');
        Schema::dropIfExists('branches');
        Schema::dropIfExists('translations');

        Schema::create('business_settings', function (Blueprint $table): void {
            $table->id();
            $table->string('type');
            $table->text('value')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('brands', function (Blueprint $table): void {
            $table->id();
            $table->string('name')->nullable();
            $table->timestamps();
        });

        Schema::create('branches', function (Blueprint $table): void {
            $table->id();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
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
            'type' => 'pnc_language',
            'value' => json_encode(['en', 'ar']),
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        Cache::put('pnc_language', ['en', 'ar']);
        Cache::put('product_brand', false);
    }

    protected function tearDown(): void
    {
        Cache::forget('pnc_language');
        Cache::forget('product_brand');
        Schema::dropIfExists('business_settings');
        Schema::dropIfExists('brands');
        Schema::dropIfExists('branches');
        Schema::dropIfExists('translations');

        parent::tearDown();
    }

    public function test_brand_add_request_requires_english_name_even_when_english_is_second(): void
    {
        $request = BrandAddRequest::create('/admin/brand/add-new', 'POST', [
            'lang' => ['ar', 'en'],
            'name' => ['ماركة', ''],
        ]);

        $validator = $this->validateFormRequest($request);

        $this->assertTrue($validator->fails());
        $this->assertSame(
            translate('The_name_in_english_is_required'),
            $validator->errors()->first('name.1')
        );
    }

    public function test_category_add_request_requires_english_name_even_when_english_is_second(): void
    {
        $request = CategoryAddRequest::create('/admin/category/store', 'POST', [
            'lang' => ['ar', 'en'],
            'name' => ['فئة', ''],
            'priority' => 1,
            'image' => 'category.webp',
        ]);

        $validator = $this->validateFormRequest($request);

        $this->assertTrue($validator->fails());
        $this->assertSame(
            translate('The_name_in_english_is_required'),
            $validator->errors()->first('name.1')
        );
    }

    public function test_branch_add_request_requires_english_name_and_address_even_when_english_is_second(): void
    {
        $request = BranchAddRequest::create('/admin/branch/add', 'POST', [
            'lang' => ['ar', 'en'],
            'branch_name' => ['الفرع الرئيسي', ''],
            'branch_address' => ['القاهرة', ''],
            'phone' => '0100000000',
            'email' => 'branch@example.com',
            'branch_country' => 'EG',
            'branch_state' => 'Cairo',
        ]);

        $validator = $this->validateFormRequest($request);

        $this->assertTrue($validator->fails());
        $this->assertSame(
            translate('The_name_in_english_is_required'),
            $validator->errors()->first('branch_name.1')
        );
        $this->assertSame(
            translate('The_branch_address_in_english_is_required'),
            $validator->errors()->first('branch_address.1')
        );
    }

    public function test_sub_category_add_request_requires_english_name_even_when_english_is_second(): void
    {
        $request = SubCategoryAddRequest::create('/admin/sub-category/store', 'POST', [
            'lang' => ['ar', 'en'],
            'name' => ['فرعية', ''],
            'priority' => 1,
            'parent_id' => 5,
        ]);

        $validator = $this->validateFormRequest($request);

        $this->assertTrue($validator->fails());
        $this->assertSame(
            translate('The_name_in_english_is_required'),
            $validator->errors()->first('name.1')
        );
    }

    public function test_help_topic_request_requires_english_question_and_answer_even_when_english_is_second(): void
    {
        $request = HelpTopicAddRequest::create('/admin/helpTopic/add-new', 'POST', [
            'lang' => ['ar', 'en'],
            'question' => ['سؤال', ''],
            'answer' => ['إجابة', ''],
            'ranking' => 1,
        ]);

        $validator = $this->validateFormRequest($request);

        $this->assertTrue($validator->fails());
        $this->assertSame(
            translate('The_question_in_english_is_required'),
            $validator->errors()->first('question.1')
        );
        $this->assertSame(
            translate('The_answer_in_english_is_required'),
            $validator->errors()->first('answer.1')
        );
    }

    public function test_wholesaler_reason_request_requires_english_title_even_when_english_is_second(): void
    {
        $request = WholesalerRegistrationReasonRequest::create('/admin/business-settings/wholesaler-registration-reason/add', 'POST', [
            'lang' => ['ar', 'en'],
            'title' => ['سبب', ''],
            'description' => ['وصف', ''],
            'priority' => 1,
        ]);

        $validator = $this->validateFormRequest($request);

        $this->assertTrue($validator->fails());
        $this->assertSame(
            translate('The_title_in_english_is_required'),
            $validator->errors()->first('title.1')
        );
    }

    public function test_shipping_method_request_requires_english_title_and_duration_even_when_english_is_second(): void
    {
        $request = ShippingMethodRequest::create('/admin/business-settings/shipping-method/index', 'POST', [
            'lang' => ['ar', 'en'],
            'title' => ['شحن', ''],
            'duration' => ['2-3 أيام', ''],
            'cost' => '90',
        ]);

        $validator = $this->validateFormRequest($request);

        $this->assertTrue($validator->fails());
        $this->assertSame(
            translate('The_title_in_english_is_required'),
            $validator->errors()->first('title.1')
        );
        $this->assertSame(
            translate('The_duration_in_english_is_required'),
            $validator->errors()->first('duration.1')
        );
    }

    public function test_branch_service_uses_default_language_index_not_request_order(): void
    {
        $request = new Request([
            'lang' => ['ar', 'en'],
            'branch_name' => ['الفرع الرئيسي', 'Main Branch'],
            'branch_country' => 'EG',
            'branch_state' => 'Cairo',
            'branch_address' => ['القاهرة', 'Cairo'],
            'zipcode' => '12345',
            'sun_branch_hours_from' => '09:00',
            'sun_branch_hours_to' => '17:00',
            'mon_branch_hours_from' => '09:00',
            'mon_branch_hours_to' => '17:00',
            'tue_branch_hours_from' => '09:00',
            'tue_branch_hours_to' => '17:00',
            'wed_branch_hours_from' => '09:00',
            'wed_branch_hours_to' => '17:00',
            'thu_branch_hours_from' => '09:00',
            'thu_branch_hours_to' => '17:00',
            'fri_branch_hours_from' => '09:00',
            'fri_branch_hours_to' => '17:00',
            'sat_branch_hours_from' => '09:00',
            'sat_branch_hours_to' => '17:00',
            'branch_latitude' => null,
            'branch_longitude' => null,
            'phone' => '0100000000',
            'email' => 'branch@example.com',
            'status' => 'active',
            'shipping_method_city' => null,
            'manager_id' => 1,
        ]);

        $data = (new BranchService())->getAddData($request);

        $this->assertSame('Main Branch', $data['branch_name']);
        $this->assertSame('Cairo', $data['branch_address']);
    }

    private function validateFormRequest(object $request): \Illuminate\Validation\Validator
    {
        $request->setContainer(app());
        $request->setRedirector(app('redirect'));

        $validator = Validator::make($request->all(), $request->rules(), $request->messages());

        if (method_exists($request, 'after')) {
            foreach ($request->after() as $afterValidationHook) {
                $validator->after($afterValidationHook);
            }
        }

        $validator->fails();

        return $validator;
    }
}
