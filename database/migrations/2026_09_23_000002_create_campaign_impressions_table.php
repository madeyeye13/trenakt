<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // One row per participant per campaign: first_seen_at is set the
        // first time the campaign appears in that participant's eligible
        // Discover list (reach), detail_opened_at is set the first time
        // they open its task-details modal (a stronger engagement signal
        // than just being listed). The unique constraint keeps every
        // subsequent page load from inflating the count.
        Schema::create('campaign_impressions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campaign_id')->constrained()->cascadeOnDelete();
            $table->foreignId('participant_id')->constrained('users')->cascadeOnDelete();
            $table->timestamp('first_seen_at');
            $table->timestamp('detail_opened_at')->nullable();
            $table->timestamps();

            $table->unique(['campaign_id', 'participant_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('campaign_impressions');
    }
};
