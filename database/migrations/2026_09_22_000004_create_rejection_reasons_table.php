<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rejection_reasons', function (Blueprint $table) {
            $table->id();
            $table->string('label');
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        $now = now();

        DB::table('rejection_reasons')->insert([
            ['label' => 'Proof provided does not match the task requirements', 'sort_order' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['label' => 'Screenshot or link is missing or invalid', 'sort_order' => 2, 'created_at' => $now, 'updated_at' => $now],
            ['label' => 'Submission appears duplicated or copied', 'sort_order' => 3, 'created_at' => $now, 'updated_at' => $now],
            ['label' => 'Task instructions were not followed', 'sort_order' => 4, 'created_at' => $now, 'updated_at' => $now],
            ['label' => 'Insufficient detail provided', 'sort_order' => 5, 'created_at' => $now, 'updated_at' => $now],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('rejection_reasons');
    }
};
