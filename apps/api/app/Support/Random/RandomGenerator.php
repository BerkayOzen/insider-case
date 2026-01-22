<?php

namespace App\Support\Random;

interface RandomGenerator
{
    public function int(int $min, int $max): int;

    public function float(): float;
}
