<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * $table->enum('type', [...]) on Postgres doesn't create a native enum -
 * it creates a plain varchar column with a CHECK constraint listing the
 * allowed values (same nuance already relied on for campaign_submissions.
 * status - see 2026_09_22_000006_update_campaign_submissions_for_review_workflow.php).
 *
 * The original wallet_transactions migration never included
 * 'referral_reward_earned' in that list, even though
 * ParticipantWalletService::creditReferralReward() has always written rows
 * with that type - so the very first real referral payout would have been
 * rejected by Postgres with a check-constraint violation. This widens the
 * constraint to actually allow it; no existing rows are touched, since
 * every value already stored is still on the (larger) allowed list.
 *
 * The constraint isn't dropped by a hardcoded guessed name - Postgres
 * auto-names an unnamed single-column CHECK as "<table>_<column>_check",
 * which is almost certainly "wallet_transactions_type_check" here, but
 * this looks it up from pg_constraint instead of assuming, so it can't
 * silently no-op (and leave the old, narrower constraint still enforced
 * alongside a new one) if the real name ever turns out to differ.
 */
return new class extends Migration
{
    public function up(): void
    {
        $this->dropTypeCheckConstraint();

        DB::statement("
            ALTER TABLE wallet_transactions ADD CONSTRAINT wallet_transactions_type_check
            CHECK (type IN (
                'wallet_funded',
                'wallet_funding_failed',
                'campaign_reservation',
                'campaign_reservation_released',
                'campaign_spend',
                'trenakt_fee',
                'manual_admin_adjustment',
                'participant_reward_earned',
                'referral_reward_earned',
                'earning_released',
                'withdrawal_requested',
                'withdrawal_processing',
                'withdrawal_successful',
                'withdrawal_failed'
            ))
        ");
    }

    public function down(): void
    {
        $this->dropTypeCheckConstraint();

        DB::statement("
            ALTER TABLE wallet_transactions ADD CONSTRAINT wallet_transactions_type_check
            CHECK (type IN (
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
                'withdrawal_failed'
            ))
        ");
    }

    /**
     * Finds whatever CHECK constraint currently governs
     * wallet_transactions.type (by inspecting the column it's actually
     * attached to, not by name) and drops it, so up()/down() can add back
     * the right list without a stale, narrower constraint left silently
     * enforcing the old one alongside it.
     */
    protected function dropTypeCheckConstraint(): void
    {
        DB::statement(<<<'SQL'
            DO $$
            DECLARE
                existing_constraint text;
            BEGIN
                SELECT con.conname
                INTO existing_constraint
                FROM pg_constraint con
                JOIN pg_attribute att
                    ON att.attrelid = con.conrelid
                    AND att.attnum = ANY (con.conkey)
                WHERE con.conrelid = 'wallet_transactions'::regclass
                  AND con.contype = 'c'
                  AND att.attname = 'type'
                LIMIT 1;

                IF existing_constraint IS NOT NULL THEN
                    EXECUTE format('ALTER TABLE wallet_transactions DROP CONSTRAINT %I', existing_constraint);
                END IF;
            END $$;
        SQL);
    }
};
