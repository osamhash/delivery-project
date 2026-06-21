<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {

            // ❌ حذف payment_status
            $table->dropColumn('payment_status');

            // ✅ إضافة payment_method
            $table->string('payment_method')
                  ->default('cash');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {

            // رجّع payment_status إذا رجعت migration
            $table->string('payment_status')->nullable();

            // احذف payment_method
            $table->dropColumn('payment_method');
        });
    }
};
