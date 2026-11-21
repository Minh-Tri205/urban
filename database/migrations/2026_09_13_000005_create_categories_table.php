<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Danh mục sản phẩm (VD: Áo thun, Quần jean)
        Schema::create('Categories', function (Blueprint $table) {
            $table->increments('CategoryID');
            $table->string('CategoryName', 100);
            $table->unsignedInteger('GroupID')->nullable();

            $table->unique(['CategoryName', 'GroupID'], 'UQ_CategoryName_GroupID');
            $table->foreign('GroupID')->references('GroupID')->on('CategoryGroups');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('Categories');
    }
};
