<?php

use App\Domains\League\Http\Controllers\LeagueController;
use App\Domains\League\Http\Controllers\MatchController;
use App\Domains\League\Http\Controllers\TeamController;
use Illuminate\Support\Facades\Route;

Route::post('/league/init', [LeagueController::class, 'init']);
Route::post('/league/play-week', [LeagueController::class, 'playWeek']);
Route::post('/league/play-all', [LeagueController::class, 'playAll']);
Route::delete('/league', [LeagueController::class, 'reset']);
Route::get('/matches/{match}', [MatchController::class, 'show']);
Route::put('/matches/{match}', [MatchController::class, 'edit']);
Route::get('/league/state', [LeagueController::class, 'state']);
Route::get('/league/fixtures', [LeagueController::class, 'fixtures']);

Route::get('/teams/defaults', [TeamController::class, 'getDefaultTeams']);
Route::get('/teams', [TeamController::class, 'getAll']);
Route::get('/teams/{team}', [TeamController::class, 'getById']);
Route::put('/teams/{team}', [TeamController::class, 'update']);
