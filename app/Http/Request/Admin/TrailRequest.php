<?php

namespace App\Http\Requests\Admin;

use Illuminate\Validation\Rule;

class TrailRequest extends AdminRequest
{
    public const DIFFICULTIES = ['mudah', 'sedang', 'sulit', 'ekstrem'];

    protected function prepareForValidation(): void
    {
        $this->merge(['is_active' => $this->boolean('is_active')]);
    }

    public function rules(): array
    {
        return [
            'mountain_id' => ['required', 'integer', 'exists:mountains,id'],
            'name' => ['required', 'string', 'max:120'],
            'slug' => [
                'nullable', 'string', 'max:140', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
                // slug jalur unik per gunung
                Rule::unique('trails', 'slug')
                    ->where('mountain_id', $this->input('mountain_id'))
                    ->ignore($this->route('trail')?->id),
            ],
            'basecamp_name' => ['nullable', 'string', 'max:150'],
            'basecamp_latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'basecamp_longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'start_elevation_mdpl' => ['nullable', 'integer', 'min:0', 'max:9000'],
            'distance_km' => ['nullable', 'numeric', 'min:0', 'max:999.99'],
            'duration_up_hours' => ['nullable', 'numeric', 'min:0', 'max:99.9'],
            'duration_down_hours' => ['nullable', 'numeric', 'min:0', 'max:99.9'],
            'difficulty' => ['nullable', Rule::in(self::DIFFICULTIES)],
            'description' => ['nullable', 'string', 'max:5000'],
            'is_active' => ['boolean'],
        ];
    }

    public function attributes(): array
    {
        return [
            'mountain_id' => 'Gunung',
            'name' => 'Nama jalur',
            'slug' => 'Slug',
            'basecamp_name' => 'Nama basecamp',
            'basecamp_latitude' => 'Lintang basecamp',
            'basecamp_longitude' => 'Bujur basecamp',
            'start_elevation_mdpl' => 'Ketinggian awal',
            'distance_km' => 'Jarak',
            'duration_up_hours' => 'Durasi naik',
            'duration_down_hours' => 'Durasi turun',
            'difficulty' => 'Tingkat kesulitan',
            'description' => 'Deskripsi',
        ];
    }
}
