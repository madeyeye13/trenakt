<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wallet_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->enum('wallet_type', ['business', 'participant']);
            $table->enum('type', [
                'wallet_funded',
                'wallet_funding_failed',
                'campaign_reservation',
                'campaign_reservation_released',
                'campaign_spend',
                'trenakt_fee',
                'manual_admin_adjustment',
                'participant_reward_earned',
                'earning_released',
                'withdrawal_requested',
                'withdrawal_processing',
                'withdrawal_successful',
                'withdrawal_failed',
            ]);
            $table->decimal('amount', 14, 2);
            $table->string('currency', 3)->default('NGN');
            $table->nullableMorphs('reference');
            $table->text('note')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wallet_transactions');
    }
};