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
            $table->text('unique_id')->unique();
            $table->foreignId('user_id');
            $table->string('category_id');
            $table->string('service_id');
            $table->string('link');
            $table->integer('quantity');
            $table->decimal('api_price',  12, 2);
            $table->decimal('charged_price',  12, 2);
            $table->string('api_order_id')->nullable();
            $table->string('status')->default('Pending');
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
