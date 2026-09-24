<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Audit trail for every AI verification attempt on a submission. Human
 * review doesn't write rows here - there's nothing to audit beyond the
 * submission's own status/rejection_reason columns, which already capture
 * that decision. One row per attempt, so if a submission is ever
 * re-evaluated the history is kept rather than overwritten.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('submission_verifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campaign_submission_id')->constrained()->cascadeOnDelete();

            // Who/what produced this row. Only 'ai' exists today, but this
            // stays a plain string (not an enum) since it's just a label.
            $table->string('verifier')->default('ai');
            $table->string('verifier_label')->nullable();

            // The outcome actually applied to the submission after
            // deterministic checks and confidence thresholds were applied -
            // approved / rejected / needs_review.
            $table->string('verdict');

            // The AI's own raw opinion before thresholds were applied, kept
            // separate from `verdict` so an admin can see when a decision
            // was overridden by the deterministic layer or a threshold.
            $table->string('ai_verdict')->nullable();
            $table->decimal('confidence', 4, 3)->nullable();

            $table->json('deterministic_checks')->nullable();
            $table->json('ai_checks')->nullable();
            $table->json('reasons')->nullable();
            $table->json('evidence_considered')->nullable();
            $table->json('raw_response')->nullable();

            // Populated whenever AI evaluation itself failed (missing/invalid
            // key, timeout, malformed response) - the submission is always
            // left at 'submitted' in that case, this just records why.
            $table->text('error')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('submission_verifications');
    }
};
