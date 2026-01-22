<?php

namespace App\Domains\League\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTeamRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'string'],
            'power' => ['sometimes', 'integer', 'min:1', 'max:100'],
        ];
    }
}
