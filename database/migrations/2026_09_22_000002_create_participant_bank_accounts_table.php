<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('participant_bank_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('bank_code');
            $table->string('bank_name');
            $table->string('account_number');
            // The account name as resolved directly from the bank via the
            // gateway's account-resolution endpoint, not typed by the user.
            $table->string('account_name');
            // Paystack transfer-recipient code, created lazily the first time
            // a payout actually needs to go out, so we don't register a
            // recipient for accounts that never withdraw.
            $table->string('recipient_code')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('participant_bank_accounts');
    }
};
