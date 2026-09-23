<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('campaign_submissions', function (Blueprint $table) {
            $table->foreignId('rejection_reason_id')->nullable()->after('rejection_reason')
                ->constrained('rejection_reasons')->nullOnDelete();
        });

        // The original migration enforced a strict one-submission-ever unique
        // constraint on (campaign_id, participant_id). That blocks the
        // "rejected tasks can be retried" rule, so we replace it with a
        // partial unique index: a participant can only have one *active*
        // (submitted/approved) submission per campaign at a time, but can
        // have any number of rejected ones on record.
        DB::statement('ALTER TABLE campaign_submissions DROP CONSTRAINT campaign_submissions_campaign_id_participant_id_unique');
        DB::statement("CREATE UNIQUE INDEX campaign_submissions_active_unique ON campaign_submissions (campaign_id, participant_id) WHERE status <> 'rejected'");
    }

    public function down(): void
    {
        DB::statement('DROP INDEX IF EXISTS campaign_submissions_active_unique');
        DB::statement('ALTER TABLE campaign_submissions ADD CONSTRAINT campaign_submissions_campaign_id_participant_id_unique UNIQUE (campaign_id, participant_id)');

        Schema::table('campaign_submissions', function (Blueprint $table) {
            $table->dropConstrainedForeignId('rejection_reason_id');
        });
    }
};
