<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('Colors', function (Blueprint $table) {
            $table->increments('ColorID');
            $table->string('ColorName', 50)->unique();
            $table->string('HexCode', 7)->nullable(); // VD: #FF5733
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('Colors');
    }
};
