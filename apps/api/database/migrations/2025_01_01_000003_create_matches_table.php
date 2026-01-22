<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('matches', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('season_id')->constrained('seasons')->cascadeOnDelete();
            $table->foreignId('week_id')->constrained('weeks')->cascadeOnDelete();
            $table->foreignId('home_team_id')->constrained('teams')->cascadeOnDelete();
            $table->foreignId('away_team_id')->constrained('teams')->cascadeOnDelete();
            $table->unsignedInteger('home_score')->nullable();
            $table->unsignedInteger('away_score')->nullable();
            $table->boolean('is_played')->default(false);
            $table->timestamps();

            $table->index(['season_id', 'week_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('matches');
    }
};
