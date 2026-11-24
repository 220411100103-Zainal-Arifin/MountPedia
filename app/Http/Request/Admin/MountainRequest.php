<?php

namespace App\Http\Requests\Admin;

use Illuminate\Validation\Rule;

class MountainRequest extends AdminRequest
{
    public function rules(): array
    {
        return [
            'region_id' => ['required', 'integer', 'exists:regions,id'],
            'name' => ['required', 'string', 'max:120'],
            'slug' => [
                'nullable', 'string', 'max:140', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
                Rule::unique('mountains', 'slug')->ignore($this->route('mountain')?->id),
            ],
            'elevation_mdpl' => ['required', 'integer', 'min:0', 'max:9000'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'managing_authority' => ['nullable', 'string', 'max:150'],
            'image_url' => ['nullable', 'url', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
        ];
    }

    public function attributes(): array
    {
        return [
            'region_id' => 'Wilayah',
            'name' => 'Nama gunung',
            'slug' => 'Slug',
            'elevation_mdpl' => 'Ketinggian',
            'latitude' => 'Lintang',
            'longitude' => 'Bujur',
            'managing_authority' => 'Pengelola',
            'image_url' => 'URL gambar',
            'description' => 'Deskripsi',
        ];
    }
}
