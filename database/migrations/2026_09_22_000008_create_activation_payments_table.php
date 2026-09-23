<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // No 'gateway' column here (unlike wallet_funding_requests): the
        // participant side of the app is Paystack-only everywhere else
        // (bank resolution, payouts), so the activation fee stays consistent
        // with that rather than supporting a gateway choice nothing needs.
        Schema::create('activation_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
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
        Schema::dropIfExists('activation_payments');
    }
};
