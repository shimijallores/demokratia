<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('upload_sessions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignId('precinct_id')->constrained()->cascadeOnDelete();
            $table->string('batch_id')->nullable();
            $table->unsignedInteger('total_chunks');
            $table->json('received_chunks');
            $table->timestamp('expires_at');
            $table->enum('status', ['active', 'finalized', 'expired'])->default('active');
            $table->timestamps();

            $table->index('precinct_id');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('upload_sessions');
    }
};
