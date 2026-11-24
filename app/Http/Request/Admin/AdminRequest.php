<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

/** Dasar form request admin: akses dijaga middleware (auth + admin), pesan validasi berbahasa Indonesia. */
abstract class AdminRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function messages(): array
    {
        return [
            'required' => ':attribute wajib diisi.',
            'string' => ':attribute harus berupa teks.',
            'integer' => ':attribute harus berupa bilangan bulat.',
            'numeric' => ':attribute harus berupa angka.',
            'url' => ':attribute harus berupa alamat URL yang valid (diawali https://).',
            'exists' => ':attribute yang dipilih tidak valid.',
            'unique' => ':attribute sudah dipakai.',
            'regex' => ':attribute hanya boleh huruf kecil, angka, dan tanda hubung (contoh: gunung-semeru).',
            'in' => ':attribute yang dipilih tidak valid.',
            'max.string' => ':attribute maksimal :max karakter.',
            'max.numeric' => ':attribute maksimal :max.',
            'min.numeric' => ':attribute minimal :min.',
            'between.numeric' => ':attribute harus di antara :min dan :max.',
        ];
    }
}
