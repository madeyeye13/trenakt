<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Mirrors business_wallets: available balance is computed from
        // wallet_transactions (wallet_type = 'participant'), and
        // reserved_balance holds the amount tied up in pending withdrawal
        // requests, exactly like business campaign reservations.
        Schema::create('participant_wallets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->decimal('reserved_balance', 14, 2)->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('participant_wallets');
    }
};
