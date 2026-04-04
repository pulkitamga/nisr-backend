<?php

namespace Tests\Feature;

use App\Http\Requests\ServiceRequestFormRequest;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class ServiceRequestFormRequestTest extends TestCase
{
    protected function setUp(): void
    {
        if (!in_array('sqlite', \PDO::getAvailableDrivers(), true)) {
            $this->markTestSkipped('pdo_sqlite extension is not available in this environment.');
        }

        parent::setUp();

        config([
            'database.default' => 'sqlite',
            'database.connections.sqlite.database' => ':memory:',
            'database.connections.sqlite.foreign_key_constraints' => false,
        ]);

        DB::purge('sqlite');
        DB::reconnect('sqlite');

        Schema::dropIfExists('services');
        Schema::dropIfExists('users');

        Schema::create('services', function (Blueprint $table) {
            $table->id();
        });

        Schema::create('users', function (Blueprint $table) {
            $table->id();
        });

        DB::table('services')->insert(['id' => 1]);
    }

    public function test_in_shop_request_does_not_require_mobile_location_fields(): void
    {
        $validator = Validator::make(
            [
                'service_id' => 1,
                'service_option' => 'in_shop',
                'agree_terms' => 1,
            ],
            $this->makeRequestWithInput([
                'service_option' => 'in_shop',
            ])->rules()
        );

        $this->assertFalse($validator->errors()->has('country'));
        $this->assertFalse($validator->errors()->has('state'));
        $this->assertFalse($validator->errors()->has('vehicle_make'));
        $this->assertFalse($validator->errors()->has('vehicle_model'));
        $this->assertFalse($validator->errors()->has('vehicle_year'));
        $this->assertFalse($validator->errors()->has('vehicle_mileage'));
    }

    public function test_mobile_request_requires_country(): void
    {
        $validator = Validator::make(
            [
                'service_id' => 1,
                'service_option' => 'mobile',
                'agree_terms' => 1,
                'state' => 'Cairo',
                'city' => 'Nasr City',
                'area' => 'Zone 1',
                'address' => 'Street 10',
                'vehicle_type' => 'Sedan',
                'vehicle_make' => 'Toyota',
                'vehicle_model' => 'Corolla',
                'vehicle_year' => 2024,
                'vehicle_mileage' => 10000,
            ],
            $this->makeRequestWithInput([
                'service_option' => 'mobile',
            ])->rules()
        );

        $this->assertTrue($validator->errors()->has('country'));
    }

    public function test_notes_field_is_optional(): void
    {
        $validator = Validator::make(
            [
                'service_id' => 1,
                'service_option' => 'in_shop',
                'agree_terms' => 1,
                'notes' => 'Customer prefers afternoon visit.',
            ],
            $this->makeRequestWithInput([
                'service_option' => 'in_shop',
            ])->rules()
        );

        $this->assertFalse($validator->errors()->has('notes'));
    }

    public function test_request_does_not_require_vehicle_type(): void
    {
        $validator = Validator::make(
            [
                'service_id' => 1,
                'service_option' => 'in_shop',
                'agree_terms' => 1,
            ],
            $this->makeRequestWithInput([
                'service_option' => 'in_shop',
            ])->rules()
        );

        $this->assertFalse($validator->errors()->has('vehicle_type'));
    }

    public function test_request_requires_terms_acceptance(): void
    {
        $validator = Validator::make(
            [
                'service_id' => 1,
                'service_option' => 'in_shop',
            ],
            $this->makeRequestWithInput([
                'service_option' => 'in_shop',
            ])->rules()
        );

        $this->assertTrue($validator->errors()->has('agree_terms'));
    }

    private function makeRequestWithInput(array $input): ServiceRequestFormRequest
    {
        $request = new ServiceRequestFormRequest();
        $request->merge($input);

        return $request;
    }
}
