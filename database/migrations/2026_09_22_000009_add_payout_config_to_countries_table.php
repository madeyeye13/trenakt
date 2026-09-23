<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('countries', function (Blueprint $table) {
            $table->boolean('payout_enabled')->default(false);
            // Free text rather than an enum, so a new gateway driver can be
            // supported later just by adding a matching class + admin option
            // instead of another migration.
            $table->string('payout_provider')->nullable();
            // The gateway secret key for THIS country's payout account.
            // Encrypted at rest via the Country model's cast. Left null for
            // a country that should fall back to the platform's default key
            // (see Country::payoutSecretKeyResolved()).
            $table->text('payout_secret_key')->nullable();
            // The country slug the gateway itself expects (e.g. Paystack's
            // "nigeria", "ghana", "kenya") - not always identical to our own
            // country name/iso_code, so it's kept editable.
            $table->string('payout_country_slug')->nullable();
            // Which withdrawal methods are available for this country, e.g.
            // ["bank"] or ["bank","mobile_money"].
            $table->json('payout_methods')->nullable();
        });

        // Nigeria already works today via the PAYSTACK_SECRET_KEY in .env -
        // this backfill keeps that working with zero admin action needed.
        // payout_secret_key is deliberately left null here; Country::
        // payoutSecretKeyResolved() falls back to the env key for Nigeria
        // specifically. Every other country needs its own key set from the
        // admin "Countries" page before it can be enabled.
        DB::table('countries')->where('iso_code', 'NG')->update([
            'payout_enabled' => true,
            'payout_provider' => 'paystack',
            'payout_country_slug' => 'nigeria',
            'payout_methods' => json_encode(['bank']),
        ]);
    }

    public function down(): void
    {
        Schema::table('countries', function (Blueprint $table) {
            $table->dropColumn(['payout_enabled', 'payout_provider', 'payout_secret_key', 'payout_country_slug', 'payout_methods']);
        });
    }
};
