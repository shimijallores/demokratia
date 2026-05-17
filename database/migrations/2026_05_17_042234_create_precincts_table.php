<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('precincts', function (Blueprint $table) {
            $table->id();
            $table->string('precinct_code')->unique();
            $table->string('name');
            $table->string('region')->nullable();
            $table->string('province')->nullable();
            $table->string('municipality')->nullable();
            $table->string('barangay')->nullable();
            $table->unsignedInteger('registered_voters')->nullable();
            $table->string('api_key_hash');
            $table->text('aes_key_encrypted');
            $table->enum('status', ['pending', 'transmitting', 'partial', 'complete', 'error'])->default('pending');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('precincts');
    }
};
