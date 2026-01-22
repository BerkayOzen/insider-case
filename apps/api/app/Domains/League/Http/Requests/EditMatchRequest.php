<?php

namespace App\Domains\League\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class EditMatchRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'home_score' => ['required', 'integer', 'min:0', 'max:10'],
            'away_score' => ['required', 'integer', 'min:0', 'max:10'],
        ];
    }
}
