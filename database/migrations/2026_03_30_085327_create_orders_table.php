<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users');
            $table->foreignId('provider_id')->constrained('providers');
            $table->foreignId('driver_id')->constrained('drivers');

            $table->foreignId('status_id')->constrained('order_status','id');

            $table->decimal('total_price', 6, 2);
            $table->string('payment_status', 100);
            $table->string('order_address', 100);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
