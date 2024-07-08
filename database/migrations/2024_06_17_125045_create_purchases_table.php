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
        Schema::create('purchases', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('voucher_no', 255);
            $table->bigInteger('supplier_id');
            $table->dateTime('purchase_date');
            $table->enum('status', ['draft', 'order', 'completed', 'cancel'])->default('draft');
            $table->dateTime('received_date')->nullable();
            $table->text('remark')->nullable();
            $table->decimal('total_price', 10, 2);
            $table->enum('payment_type', ['cash', 'online'])->default('cash');
            $table->bigInteger('created_by')->unsigned()->nullable();
            $table->bigInteger('updated_by')->unsigned()->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchases');
    }
};
