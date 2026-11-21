<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Giỏ hàng chưa checkout, mỗi khách hàng chỉ có 1 giỏ
        Schema::create('Carts', function (Blueprint $table) {
            $table->increments('CartID');
            $table->unsignedInteger('CustomerID')->nullable()->unique('UQ_Customer_Cart');
            $table->string('CartToken', 64)->unique(); // Lưu ở cookie/session cho khách chưa đăng nhập
            $table->dateTime('CreatedAt')->nullable()->useCurrent();
            $table->dateTime('UpdatedAt')->nullable()->useCurrent();

            $table->foreign('CustomerID')->references('CustomerID')->on('Customers')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('Carts');
    }
};
