<?php

namespace App\Domains\League\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class InitLeagueRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'string'],
            'teams' => ['sometimes', 'array', 'size:4'],
            'teams.*.name' => ['required_with:teams', 'string'],
            'teams.*.power' => ['required_with:teams', 'integer', 'min:1', 'max:100'],
        ];
    }
}
