<?php

namespace App\Http\Requests\Api\Lesson;

use Illuminate\Foundation\Http\FormRequest;

class SyncProgressRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'watch_time_in_seconds' => ['required', 'integer', 'min:0'],
            'is_completed' => ['required', 'boolean'],
        ];
    }
}