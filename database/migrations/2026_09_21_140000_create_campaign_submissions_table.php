<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('campaign_submissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campaign_id')->constrained()->cascadeOnDelete();
            $table->foreignId('participant_id')->constrained('users')->cascadeOnDelete();
            $table->enum('status', ['submitted', 'approved', 'rejected'])->default('submitted');
            $table->json('answers');
            $table->timestamp('submitted_at');
            $table->timestamp('reviewed_at')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->timestamps();

            $table->unique(['campaign_id', 'participant_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('campaign_submissions');
    }
};
