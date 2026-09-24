<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Lets an admin claw back a reward after a submission was already approved
 * and paid, without touching the submission's own status. It stays
 * 'approved' - the task really was completed and verified correctly at the
 * time - because a revocation is a separate, later event (e.g. fraud
 * discovered afterwards, a policy violation, a chargeback upstream), not a
 * correction of the original review decision. That's also why this doesn't
 * reopen the campaign slot or let the participant resubmit: only a genuine
 * rejection does that (see TaskService::participantIsEligible()).
 *
 * The actual money movement is a negative manual_admin_adjustment
 * WalletTransaction row (see ParticipantWalletService::revokeReward()) -
 * these three columns are just the audit trail on the submission itself.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('campaign_submissions', function (Blueprint $table) {
            $table->timestamp('reward_revoked_at')->nullable()->after('rejection_reason_id');
            $table->text('reward_revocation_reason')->nullable()->after('reward_revoked_at');
            $table->foreignId('revoked_by')->nullable()->after('reward_revocation_reason')
                ->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('campaign_submissions', function (Blueprint $table) {
            $table->dropConstrainedForeignId('revoked_by');
            $table->dropColumn(['reward_revoked_at', 'reward_revocation_reason']);
        });
    }
};
