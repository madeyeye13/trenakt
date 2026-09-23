<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('campaigns', function (Blueprint $table) {
            // An ordered list of plain-text instructions the business writes
            // when creating the campaign (e.g. "Click the link above",
            // "Sign up with your real email", "Take a screenshot of the
            // confirmation page"). Shown to participants as a numbered
            // how-to, instead of us guessing at instructional copy or
            // reusing a category field's label (written for the business,
            // not the participant) as if it were one.
            $table->json('steps')->nullable()->after('description');
        });
    }

    public function down(): void
    {
        Schema::table('campaigns', function (Blueprint $table) {
            $table->dropColumn('steps');
        });
    }
};
