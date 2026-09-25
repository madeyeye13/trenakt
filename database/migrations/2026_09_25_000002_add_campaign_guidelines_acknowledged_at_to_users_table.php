<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Set the first time a business closes the "campaign guidelines"
     * modal shown on the Create Campaign page (dos/don'ts, wallet funds
     * tied to a violation aren't withdrawable). Stored server-side rather
     * than in browser storage so it persists across devices and doubles
     * as a record that they were shown and acknowledged the rules.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->timestamp('campaign_guidelines_acknowledged_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('campaign_guidelines_acknowledged_at');
        });
    }
};
