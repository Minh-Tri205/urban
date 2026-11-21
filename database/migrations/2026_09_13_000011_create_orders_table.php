<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('Orders', function (Blueprint $table) {
            $table->increments('OrderID');
            $table->unsignedInteger('CustomerID')->nullable();
            $table->dateTime('OrderDate')->nullable()->useCurrent();
            $table->string('Status', 50)->default('Pending'); // Pending, Processing, Shipped, Completed, Cancelled
            $table->string('FullName', 100)->nullable();
            $table->string('PhoneNumber', 20)->nullable();
            $table->string('ShippingAddress', 255);
            $table->decimal('TotalAmount', 18, 2);
            $table->string('PaymentMethod', 50)->nullable(); // COD/Chuyển khoản/Ví điện tử
            $table->string('ShippingMethod', 50)->default('Standard');
            $table->decimal('ShippingFee', 18, 2)->default(0);

            $table->foreign('CustomerID')->references('CustomerID')->on('Customers');
        });

        DB::statement('ALTER TABLE Orders ADD CONSTRAINT CK_Orders_ShippingFee_NonNegative CHECK (ShippingFee >= 0)');
    }

    public function down(): void
    {
        Schema::dropIfExists('Orders');
    }
};
