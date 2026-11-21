<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Lịch sử thay đổi trạng thái đơn hàng (OrderService::logStatusChange)
        Schema::create('OrderStatusHistory', function (Blueprint $table) {
            $table->increments('HistoryID');
            $table->unsignedInteger('OrderID');
            $table->string('OldStatus', 50)->nullable();
            $table->string('NewStatus', 50);
            $table->string('ChangedBy', 100)->default('System');
            $table->dateTime('ChangedAt')->useCurrent();
            $table->string('Note', 500)->nullable();

            $table->foreign('OrderID')->references('OrderID')->on('Orders')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('OrderStatusHistory');
    }
};
