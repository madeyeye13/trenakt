<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * task_mode / platforms / post_content_* only ever get set on a
     * campaign whose category has supports_post_modes on - see the
     * migration that adds that flag. Left null for every other campaign,
     * old and new, so nothing downstream that reads rate_per_participant,
     * steps, etc. needs to know these columns exist.
     *
     * completed_at fills a real gap: 'completed' has been a valid status
     * in the campaigns.status enum since the table was created, but
     * nothing in the app ever actually set it - see
     * App\Console\Commands\CompleteReachedCampaigns, added alongside this
     * migration, which is the first thing that does.
     */
    public function up(): void
    {
        Schema::table('campaigns', function (Blueprint $table) {
            $table->string('task_mode')->nullable()->after('steps');
            $table->json('platforms')->nullable()->after('task_mode');
            $table->text('post_content_text')->nullable()->after('platforms');
            $table->string('post_content_media')->nullable()->after('post_content_text');
            $table->timestamp('completed_at')->nullable()->after('reviewed_by');
        });
    }

    public function down(): void
    {
        Schema::table('campaigns', function (Blueprint $table) {
            $table->dropColumn(['task_mode', 'platforms', 'post_content_text', 'post_content_media', 'completed_at']);
        });
    }
};
