<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('CartItems', function (Blueprint $table) {
            $table->increments('CartItemID');
            $table->unsignedInteger('CartID');
            $table->unsignedInteger('VariantID');
            $table->integer('Quantity');
            $table->dateTime('AddedAt')->nullable()->useCurrent();

            $table->unique(['CartID', 'VariantID'], 'UQ_Cart_Variant');
            $table->foreign('CartID')->references('CartID')->on('Carts')->cascadeOnDelete();
            $table->foreign('VariantID')->references('VariantID')->on('ProductVariants');
        });

        DB::statement('ALTER TABLE CartItems ADD CONSTRAINT CK_CartItems_Quantity CHECK (Quantity > 0)');
    }

    public function down(): void
    {
        Schema::dropIfExists('CartItems');
    }
};
