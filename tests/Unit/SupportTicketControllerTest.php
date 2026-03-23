<?php

namespace Tests\Unit;

use App\Contracts\Repositories\AdminNotificationRepositoryInterface;
use App\Contracts\Repositories\AdminRepositoryInterface;
use App\Contracts\Repositories\DepartmentRepositoryInterface;
use App\Contracts\Repositories\SupportTicketActivityRepositoryInterface;
use App\Contracts\Repositories\SupportTicketConvRepositoryInterface;
use App\Contracts\Repositories\SupportTicketRepositoryInterface;
use App\Http\Controllers\Admin\HelpAndSupport\SupportTicketController;
use App\Services\Crm\EscalationService;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class SupportTicketControllerTest extends TestCase
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

        Schema::create('business_settings', function (Blueprint $table) {
            $table->id();
            $table->string('type')->nullable();
            $table->longText('value')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('translations', function (Blueprint $table) {
            $table->id();
            $table->string('translationable_type');
            $table->unsignedBigInteger('translationable_id');
            $table->string('locale');
            $table->string('key');
            $table->text('value')->nullable();
            $table->timestamps();
        });

        Schema::create('services', function (Blueprint $table) {
            $table->id();
            $table->string('title')->nullable();
            $table->timestamps();
        });

        Schema::create('support_ticket_status_master', function (Blueprint $table) {
            $table->unsignedBigInteger('id')->primary();
            $table->unsignedInteger('master_id')->nullable();
            $table->string('name');
            $table->string('status')->nullable();
            $table->unsignedInteger('position')->nullable();
        });

        DB::table('business_settings')->insert([
            'type' => 'pagination_limit',
            'value' => '15',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function test_service_list_defaults_to_all_status_filter(): void
    {
        $ticketRepo = $this->createMock(SupportTicketRepositoryInterface::class);
        $ticketRepo->expects($this->once())
            ->method('getListWhere')
            ->with(
                ['id' => 'desc'],
                null,
                $this->callback(function (array $filters) {
                    return ($filters['type'] ?? null) === 'service'
                        && ($filters['status'] ?? null) === 'all';
                }),
                $this->callback(function (array $relations) {
                    return in_array('service', $relations, true)
                        && in_array('latestServiceJob', $relations, true)
                        && in_array('latestServiceJob.service', $relations, true);
                }),
                getWebConfig('pagination_limit')
            )
            ->willReturn(new LengthAwarePaginator([], 0, getWebConfig('pagination_limit')));

        $departmentRepo = $this->createMock(DepartmentRepositoryInterface::class);
        $departmentRepo->expects($this->once())
            ->method('getListWhere')
            ->willReturn(new EloquentCollection());

        $adminRepo = $this->createMock(AdminRepositoryInterface::class);
        $adminRepo->expects($this->once())
            ->method('getEmployeeListWhere')
            ->willReturn(new EloquentCollection());

        $controller = new SupportTicketController(
            $ticketRepo,
            $this->createMock(SupportTicketConvRepositoryInterface::class),
            $departmentRepo,
            $adminRepo,
            $this->createMock(SupportTicketActivityRepositoryInterface::class),
            $this->createMock(AdminNotificationRepositoryInterface::class),
            $this->createMock(EscalationService::class),
        );

        $view = $controller->getListView(new Request(), 'service');

        $this->assertSame('service', $view->getData()['status']);
    }
}
