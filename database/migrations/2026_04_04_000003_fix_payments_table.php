<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            // إزالة الأعمدة الخاطئة
            if (Schema::hasColumn('payments', 'provider_id')) {
                $table->dropForeign(['provider_id']);
                $table->dropColumn('provider_id');
            }
            if (Schema::hasColumn('payments', 'name')) {
                $table->dropColumn('name');
            }

            // إضافة الأعمدة الصحيحة
            if (!Schema::hasColumn('payments', 'order_id')) {
                $table->foreignId('order_id')->after('user_id')->constrained('orders')->onDelete('cascade');
            }
            if (!Schema::hasColumn('payments', 'method')) {
                $table->enum('method', ['cash', 'card'])->default('cash')->after('driver_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropForeign(['order_id']);
            $table->dropColumn(['order_id', 'method']);
            $table->foreignId('provider_id')->constrained('providers');
            $table->string('name');
        });
    }
};
