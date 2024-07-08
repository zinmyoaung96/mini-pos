<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('sales', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('voucher_no', 255);
            $table->bigInteger('customer_id');
            $table->bigInteger('floor_id')->nullable();
            $table->bigInteger('table_id')->nullable();
            $table->dateTime('sale_date');
            $table->enum('status', ['ordered', 'preparing', 'served', 'completed', 'canceled'])->default('ordered');
            $table->enum('payment_status', ['pending', 'paid', 'refunded']);
            $table->text('remark')->nullable();
            $table->decimal('total_price', 10, 2);
            $table->enum('payment_type', ['cash', 'online'])->default('cash');
            $table->decimal('paid_amount', 10,2)->nullable();
            $table->decimal('balance_amount', 10,2)->nullable();
            $table->bigInteger('created_by')->nullable();
            $table->bigInteger('updated_by')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sales');
    }
};
