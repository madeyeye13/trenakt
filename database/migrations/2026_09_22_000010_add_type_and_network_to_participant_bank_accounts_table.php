<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('participant_bank_accounts', function (Blueprint $table) {
            // 'bank' (a bank account) or 'mobile_money' (a mobile wallet
            // like MTN/Vodafone/AirtelTigo) - which recipient type this
            // record represents at the gateway.
            $table->string('type')->default('bank')->after('user_id');
            // The mobile network for a mobile_money account (e.g. "MTN"),
            // null for a bank account.
            $table->string('network')->nullable()->after('bank_name');
        });
    }

    public function down(): void
    {
        Schema::table('participant_bank_accounts', function (Blueprint $table) {
            $table->dropColumn(['type', 'network']);
        });
    }
};
