<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('candidates', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->enum('position', ['president', 'vice_president', 'senator', 'party_list']);
            $table->string('party')->nullable();
            $table->string('ballot_number');
            $table->string('photo_url')->nullable();
            $table->timestamps();

            $table->index(['position', 'ballot_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('candidates');
    }
};
