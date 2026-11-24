<?php

namespace App\Http\Requests\Admin;

class CampsiteRequest extends AdminRequest
{
    public function rules(): array
    {
        return [
            'mountain_id'      => ['required', 'integer', 'exists:mountains,id'],
            'trail_id'         => ['nullable', 'integer', 'exists:trails,id'],
            'name'             => ['required', 'string', 'max:120'],
            'elevation_mdpl'   => ['nullable', 'integer', 'min:0', 'max:9000'],
            'capacity_tents'   => ['nullable', 'integer', 'min:1', 'max:9999'],
            'has_water_source' => ['boolean'],
            'water_note'       => ['nullable', 'string', 'max:255'],
            'latitude'         => ['nullable', 'numeric', 'between:-90,90'],
            'longitude'        => ['nullable', 'numeric', 'between:-180,180'],
            'notes'            => ['nullable', 'string', 'max:2000'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'has_water_source' => $this->boolean('has_water_source'),
        ]);
    }

    public function attributes(): array
    {
        return [
            'mountain_id'      => 'Gunung',
            'trail_id'         => 'Jalur',
            'name'             => 'Nama campsite',
            'elevation_mdpl'   => 'Ketinggian',
            'capacity_tents'   => 'Kapasitas tenda',
            'has_water_source' => 'Sumber air',
            'water_note'       => 'Catatan sumber air',
            'latitude'         => 'Lintang',
            'longitude'        => 'Bujur',
            'notes'            => 'Catatan',
        ];
    }
}
