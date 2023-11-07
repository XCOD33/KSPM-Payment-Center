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
        Schema::create('pembayaran_users', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pembayaran_id')->constrained('pembayarans')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('invoice_id');
            $table->string('uuid');
            $table->string('payment_method')->nullable();
            $table->string('payment_method_code')->nullable();
            $table->bigInteger('total_fee')->nullable();
            $table->bigInteger('subtotal');
            $table->bigInteger('total')->nullable();
            $table->enum('status', ['FAILED', 'PAID', 'EXPIRED', 'UNPAID'])->default('UNPAID');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pembayaran_users');
    }
};
