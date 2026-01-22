<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('standings', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('season_id')->constrained('seasons')->cascadeOnDelete();
            $table->foreignId('team_id')->constrained('teams')->cascadeOnDelete();
            $table->unsignedInteger('played')->default(0);
            $table->unsignedInteger('won')->default(0);
            $table->unsignedInteger('drawn')->default(0);
            $table->unsignedInteger('lost')->default(0);
            $table->unsignedInteger('gf')->default(0);
            $table->unsignedInteger('ga')->default(0);
            $table->integer('gd')->default(0);
            $table->unsignedInteger('points')->default(0);
            $table->timestamps();

            $table->unique(['season_id', 'team_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('standings');
    }
};
