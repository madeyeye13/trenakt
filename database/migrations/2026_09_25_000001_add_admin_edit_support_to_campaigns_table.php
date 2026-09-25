<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * allow_admin_edit is set by the business at campaign creation time (a
     * checkbox, off by default) and never changed afterward by anyone but
     * them re-submitting. It controls whether admin's campaign review
     * screen unlocks the title/description/steps fields for editing before
     * approval, instead of only offering approve/reject on what was
     * submitted.
     *
     * original_content stores a one-time snapshot of the pre-edit
     * title/description/steps, captured the first time an admin actually
     * saves an edit (not on every edit), so the business can always see
     * what they originally wrote even if it's edited more than once.
     */
    public function up(): void
    {
        Schema::table('campaigns', function (Blueprint $table) {
            $table->boolean('allow_admin_edit')->default(false)->after('steps');
            $table->timestamp('admin_edited_at')->nullable()->after('reviewed_by');
            $table->foreignId('admin_edited_by')->nullable()->after('admin_edited_at')->constrained('users')->nullOnDelete();
            $table->json('original_content')->nullable()->after('admin_edited_by');
        });
    }

    public function down(): void
    {
        Schema::table('campaigns', function (Blueprint $table) {
            $table->dropConstrainedForeignId('admin_edited_by');
            $table->dropColumn(['allow_admin_edit', 'admin_edited_at', 'original_content']);
        });
    }
};
