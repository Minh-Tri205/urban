<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Sản phẩm chính: chỉ lưu thông tin chung, màu/size/tồn kho nằm ở ProductVariants
        Schema::create('Products', function (Blueprint $table) {
            $table->increments('ProductID');
            $table->string('ProductName', 200);
            $table->longText('Description')->nullable();
            $table->decimal('BasePrice', 18, 2);
            $table->decimal('DiscountPercent', 5, 2)->nullable()->default(0);
            $table->unsignedInteger('CategoryID')->nullable();
            $table->boolean('IsActive')->nullable()->default(true);
            $table->dateTime('CreatedAt')->nullable()->useCurrent();

            $table->foreign('CategoryID')->references('CategoryID')->on('Categories');
        });

        DB::statement('ALTER TABLE Products ADD CONSTRAINT CK_Products_DiscountPercent CHECK (DiscountPercent >= 0 AND DiscountPercent <= 100)');
    }

    public function down(): void
    {
        Schema::dropIfExists('Products');
    }
};
