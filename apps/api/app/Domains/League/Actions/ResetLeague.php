<?php

namespace App\Domains\League\Actions;

use App\Domains\League\Models\Season;
use Illuminate\Support\Facades\DB;

class ResetLeague
{
    public function execute(): void
    {
        DB::transaction(function (): void {
            DB::table('matches')->delete();
            DB::table('standings')->delete();
            DB::table('weeks')->delete();
            DB::table('teams')->delete();
            Season::query()->delete();
        });
    }
}
