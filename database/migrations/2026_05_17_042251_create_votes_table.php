<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('votes', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('batch_id')->constrained('batches')->cascadeOnDelete();
            $table->foreignId('candidate_id')->constrained()->cascadeOnDelete();
            $table->foreignId('precinct_id')->constrained()->cascadeOnDelete();
            $table->string('position');
            $table->timestamps();

            $table->index(['candidate_id', 'position']);
            $table->index('batch_id');
            $table->index('precinct_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('votes');
    }
};
