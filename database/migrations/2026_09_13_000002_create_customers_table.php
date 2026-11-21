<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Thông tin đăng nhập khách hàng
        Schema::create('Customers', function (Blueprint $table) {
            $table->increments('CustomerID');
            $table->string('UserName', 50)->unique();
            $table->string('PasswordHash', 255);
            $table->string('Email', 100)->unique();
            $table->boolean('IsActive')->nullable()->default(true);
            $table->dateTime('CreatedAt')->nullable()->useCurrent();
            $table->unsignedInteger('RoleID')->nullable();

            $table->foreign('RoleID', 'FK_Customers_Role')->references('RoleID')->on('Roles');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('Customers');
    }
};
