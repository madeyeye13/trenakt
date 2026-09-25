<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * For a reshare-mode campaign, the business's own already-live post
     * has a different URL on each platform it's live on - one generic
     * link (post_content_text/the category's business requirement field)
     * can't stand in for a Facebook post AND an Instagram post AND a
     * TikTok post at once. This holds one source link per selected
     * platform instead, keyed the same way as platforms/platform_links
     * elsewhere: {"facebook": "https://...", "tiktok": "https://..."}.
     *
     * Only ever set for task_mode === 'reshare' - see
     * Livewire\Campaigns\Create::submit(). Null for post_own_content
     * (the business supplies post_content_text/post_content_media
     * instead, which is the same for every platform since participants
     * post it themselves) and for every campaign whose category doesn't
     * support post modes at all.
     */
    public function up(): void
    {
        Schema::table('campaigns', function (Blueprint $table) {
            $table->json('platform_source_links')->nullable()->after('post_content_media');
        });
    }

    public function down(): void
    {
        Schema::table('campaigns', function (Blueprint $table) {
            $table->dropColumn('platform_source_links');
        });
    }
};
