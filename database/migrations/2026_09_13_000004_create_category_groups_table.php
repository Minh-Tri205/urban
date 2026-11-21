<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Nhóm danh mục (VD: Thời trang nam, Thời trang nữ)
        Schema::create('CategoryGroups', function (Blueprint $table) {
            $table->increments('GroupID');
            $table->string('GroupName', 100)->unique();
            $table->longText('Description')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('CategoryGroups');
    }
};
