<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('category_requirement_fields', function (Blueprint $table) {
            $table->enum('fills_for', ['business', 'participant'])->default('participant')->after('type');
        });
    }

    public function down(): void
    {
        Schema::table('category_requirement_fields', function (Blueprint $table) {
            $table->dropColumn('fills_for');
        });
    }
};