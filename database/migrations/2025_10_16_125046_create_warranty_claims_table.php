<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('warranty_claims', function (Blueprint $table) {
            $table->id();
            $table->foreignId('warranty_id')->constrained('warranties')->onDelete('cascade');
            $table->string('serial_number');
            $table->string('claim_number')->unique();
            $table->enum('status', ['new', 'triage_pending', 'approved', 'rma_issued', 'received', 'diagnosis_pending', 'repair_pending', 'replacement_pending', 'shipped_ready', 'resolved', 'closed', 'rejected', 'waiting_customer', 'waiting_parts', 'waiting_payment'])->default('new');
            $table->text('description');
            $table->timestamp('submitted_at')->useCurrent();
            $table->timestamp('first_response_due')->nullable();
            $table->timestamp('decision_due')->nullable();
            $table->string('rma_number')->nullable();
            $table->timestamp('rma_deadline')->nullable();
            $table->timestamp('received_at')->nullable();
            $table->foreignId('branch_id')->nullable()->constrained('branches')->onDelete('set null');
            $table->foreignId('technician_id')->nullable()->constrained('users')->onDelete('set null');  // User as technician
            $table->text('diagnosis_notes')->nullable();
            $table->enum('repair_or_replace', ['repair', 'replace'])->nullable();
            $table->boolean('is_admin_override')->default(false);
            $table->text('override_reason')->nullable();
            $table->foreignId('override_by_user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->integer('reopen_count')->default(0);
            $table->boolean('inspection_fee_due')->default(false);
            $table->boolean('is_fee_waived')->default(false);
            $table->float('inspection_fee_amount')->nullable();
            $table->json('attachments')->nullable();  // Array of paths
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('warranty_claims');
    }
};