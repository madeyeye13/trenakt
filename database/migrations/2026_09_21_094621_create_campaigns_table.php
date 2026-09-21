<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('campaigns', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('campaign_category_id')->constrained();
            $table->string('title');
            $table->text('description')->nullable();
            $table->decimal('rate_per_participant', 12, 2);
            $table->unsignedInteger('target_participants');
            $table->decimal('total_budget', 12, 2);
            $table->decimal('platform_fee_amount', 12, 2);
            $table->enum('status', ['draft', 'pending_review', 'approved', 'rejected', 'completed', 'cancelled'])->default('draft');
            $table->text('rejection_reason')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('campaigns');
    }
};