<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('weeks', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('season_id')->constrained('seasons')->cascadeOnDelete();
            $table->unsignedInteger('number');
            $table->boolean('is_played')->default(false);
            $table->timestamps();

            $table->unique(['season_id', 'number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('weeks');
    }
};
