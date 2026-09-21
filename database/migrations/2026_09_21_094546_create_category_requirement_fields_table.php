<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('category_requirement_fields', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campaign_category_id')->constrained()->cascadeOnDelete();
            $table->string('label');
            $table->string('field_key');
            $table->enum('type', ['text', 'textarea', 'file', 'url', 'number']);
            $table->boolean('is_required')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('category_requirement_fields');
    }
};