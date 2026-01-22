<?php

namespace App\Support\Random;

class PhpRandomGenerator implements RandomGenerator
{
    public function int(int $min, int $max): int
    {
        return random_int($min, $max);
    }

    public function float(): float
    {
        return mt_rand() / mt_getrandmax();
    }
}
