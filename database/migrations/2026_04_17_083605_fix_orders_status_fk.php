<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {

            // فقط إنشاء العلاقة (بدون حذف)
            $table->foreign('status_id')
                ->references('id')
                ->on('order_status')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['status_id']);
        });
    }
};
