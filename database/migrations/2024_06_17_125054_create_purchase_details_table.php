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
        Schema::create('purchase_details', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->enum('status', ['draft', 'order', 'completed', 'cancel'])->default('draft');
            $table->bigInteger('purchase_id')->nullable();
            $table->bigInteger('product_id')->nullable();
            $table->decimal('purchase_price', 10, 2)->nullable();
            $table->integer('quantity');
            $table->enum('unit', ['piece', 'kg', 'liter', 'gram', 'meter', 'box'])->default('piece');
            $table->decimal('subtotal_price', 10, 2)->nullable();
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
        Schema::dropIfExists('purchase_details');
    }
};
