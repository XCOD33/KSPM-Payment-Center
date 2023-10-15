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
        Schema::create('pembayarans', function (Blueprint $table) {
            $table->id();
            $table->string('uuid')->unique();
            $table->string('url')->unique();
            $table->string('name');
            $table->bigInteger('nominal');
            $table->text('description');
            $table->enum('status', ['active', 'inactive']);
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->datetime('expired_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pembayarans');
    }
};
