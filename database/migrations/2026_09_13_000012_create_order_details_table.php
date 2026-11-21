<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Lưu giá và % giảm giá tại thời điểm mua
        Schema::create('OrderDetails', function (Blueprint $table) {
            $table->increments('OrderDetailID');
            $table->unsignedInteger('OrderID')->nullable();
            $table->unsignedInteger('VariantID')->nullable();
            $table->integer('Quantity');
            $table->decimal('Price', 18, 2);
            $table->decimal('DiscountPercent', 5, 2)->nullable()->default(0);

            $table->foreign('OrderID')->references('OrderID')->on('Orders')->cascadeOnDelete();
            $table->foreign('VariantID')->references('VariantID')->on('ProductVariants');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('OrderDetails');
    }
};
