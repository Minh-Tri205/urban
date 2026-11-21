<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Mỗi sản phẩm + màu + size = 1 biến thể có SKU và tồn kho riêng
        Schema::create('ProductVariants', function (Blueprint $table) {
            $table->increments('VariantID');
            $table->unsignedInteger('ProductID');
            $table->unsignedInteger('ColorID');
            $table->unsignedInteger('SizeID');
            $table->string('SKU', 50)->unique();
            $table->integer('Stock')->default(0);
            $table->string('Status', 50)->default('Available'); // Available, OutOfStock, Discontinued

            $table->unique(['ProductID', 'ColorID', 'SizeID'], 'UQ_Product_Color_Size');
            $table->foreign('ProductID')->references('ProductID')->on('Products')->cascadeOnDelete();
            $table->foreign('ColorID')->references('ColorID')->on('Colors');
            $table->foreign('SizeID')->references('SizeID')->on('Sizes');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ProductVariants');
    }
};
