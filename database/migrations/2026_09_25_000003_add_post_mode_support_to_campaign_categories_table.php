<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * supports_post_modes gates the "reshare vs post on your own page"
     * choice and the platform multi-select at campaign creation - both
     * only appear for categories admin has switched this on for, so every
     * other category's creation form is completely unaffected.
     *
     * platform_bonus_amount is a flat NGN amount added to the participant
     * rate for each social platform beyond the first one a business
     * selects (see CampaignService::calculateBudget()). Admin sets it
     * once per category; 0 means no bonus, which is also the default so
     * existing categories behave exactly as before this migration.
     */
    public function up(): void
    {
        Schema::table('campaign_categories', function (Blueprint $table) {
            $table->boolean('supports_post_modes')->default(false)->after('is_active');
            $table->decimal('platform_bonus_amount', 12, 2)->default(0)->after('supports_post_modes');
        });
    }

    public function down(): void
    {
        Schema::table('campaign_categories', function (Blueprint $table) {
            $table->dropColumn(['supports_post_modes', 'platform_bonus_amount']);
        });
    }
};
