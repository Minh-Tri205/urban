<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1 = Admin, 2 = Nhân viên, 3 = Khách hàng
        Schema::create('Roles', function (Blueprint $table) {
            $table->increments('RoleID');
            $table->string('RoleName', 50)->unique();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('Roles');
    }
};
