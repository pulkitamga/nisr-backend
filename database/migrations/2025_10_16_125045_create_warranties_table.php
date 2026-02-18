<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('warranties', function (Blueprint $table) {
            $table->id();
            $table->string('serial_number')->unique();
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->foreignId('product_stock_id')->nullable()->constrained('product_stocks')->onDelete('set null');  // Link to stock instance
            $table->enum('status', ['preactivated', 'active', 'cancelled', 'replaced', 'expired'])->default('preactivated');
            $table->timestamp('activation_date')->nullable();
            $table->timestamp('start_date')->nullable();
            $table->timestamp('end_date')->nullable();
            $table->foreignId('final_user_id')->nullable()->constrained('users')->onDelete('set null');  // Customer
            $table->unsignedBigInteger('distributor_id')->nullable();  // Assume Distributor model if exists, else integer
            $table->foreignId('branch_id')->nullable()->constrained('branches')->onDelete('set null');
            $table->string('activated_by_name')->nullable();
            $table->string('activated_by_phone')->nullable();
            $table->string('activated_by_email')->nullable();
            $table->string('activated_ip')->nullable();
            $table->string('activation_method')->nullable();  // user_profile, user_public_form, admin_manual, replacement
            $table->boolean('is_admin_manual_activation')->default(false);
            $table->boolean('is_admin_override')->default(false);
            $table->foreignId('original_warranty_id')->nullable()->constrained('warranties')->onDelete('cascade');  // For replacements
            $table->date('purchase_date')->nullable();
            $table->string('retailer_name')->nullable();
            $table->unsignedBigInteger('retailer_branch_id')->nullable();
            $table->string('invoice_number')->nullable();
            $table->string('receipt_path')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('warranties');
    }
};