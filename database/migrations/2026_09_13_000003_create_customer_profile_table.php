<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Thông tin bổ sung khách hàng (quan hệ 1-1 với Customers)
        Schema::create('CustomerProfile', function (Blueprint $table) {
            $table->unsignedInteger('ProfileID')->primary();
            $table->string('FullName', 100)->nullable();
            $table->string('PhoneNumber', 20)->nullable();
            $table->date('DateOfBirth')->nullable();
            $table->string('Gender', 10)->nullable(); // Nam, Nữ, Khác
            $table->string('Address', 255)->nullable();
            $table->string('City', 100)->nullable();
            $table->string('District', 100)->nullable();

            $table->foreign('ProfileID')->references('CustomerID')->on('Customers')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('CustomerProfile');
    }
};
