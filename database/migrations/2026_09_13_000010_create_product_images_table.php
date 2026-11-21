<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ProductImages', function (Blueprint $table) {
            $table->increments('ImageID');
            $table->unsignedInteger('ProductID');
            $table->string('ImageUrl', 500);
            $table->boolean('IsPrimary')->nullable()->default(false); // Ảnh chính làm thumbnail

            $table->foreign('ProductID')->references('ProductID')->on('Products')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ProductImages');
    }
};
