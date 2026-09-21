<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wallet_funding_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->enum('gateway', ['paystack', 'flutterwave']);
            $table->string('reference')->unique();
            $table->decimal('amount', 14, 2);
            $table->enum('status', ['pending', 'successful', 'failed'])->default('pending');
            $table->string('gateway_transaction_id')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wallet_funding_requests');
    }
};