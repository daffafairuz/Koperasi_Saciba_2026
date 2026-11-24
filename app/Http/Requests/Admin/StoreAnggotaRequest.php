<?php

namespace App\Http\Requests\Admin;

use App\Models\User;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAnggotaRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return (bool) $this->user()?->isAdmin();
    }

    /**
     * Normalisasi input sebelum aturan validasi (termasuk unique no_hp) dijalankan.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'nomor_anggota' => is_string($this->nomor_anggota) ? trim($this->nomor_anggota) : $this->nomor_anggota,
            'nama_lengkap' => is_string($this->nama_lengkap) ? trim($this->nama_lengkap) : $this->nama_lengkap,
            'no_hp' => is_string($this->no_hp) ? $this->normalizePhoneNumber($this->no_hp) : $this->no_hp,
            'wajib_simpanan' => $this->boolean('wajib_simpanan'),
        ]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'nomor_anggota' => ['required', 'string', 'max:20', 'unique:anggota,nomor_anggota'],
            'nama_lengkap' => ['required', 'string', 'max:255'],
            'no_hp' => ['required', 'string', 'max:20', 'unique:anggota,no_hp'],
            'password' => ['required', 'string', 'min:6'],
            'peran' => ['required', 'string', Rule::in([User::PERAN_ADMIN, User::PERAN_ANGGOTA])],
            'wajib_simpanan' => ['required', 'boolean'],
            'tanggal_gabung' => ['required', 'date'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'nomor_anggota.required' => 'Nomor anggota wajib diisi.',
            'nomor_anggota.unique' => 'Nomor anggota sudah terdaftar.',
            'nama_lengkap.required' => 'Nama lengkap wajib diisi.',
            'no_hp.required' => 'Nomor HP wajib diisi.',
            'no_hp.unique' => 'Nomor HP sudah digunakan oleh anggota lain.',
            'password.required' => 'Kata sandi awal wajib diisi.',
            'password.min' => 'Kata sandi minimal 6 karakter.',
            'peran.in' => 'Peran harus ADMIN atau ANGGOTA.',
            'tanggal_gabung.required' => 'Tanggal gabung wajib diisi.',
            'tanggal_gabung.date' => 'Format tanggal gabung tidak valid.',
        ];
    }

    private function normalizePhoneNumber(string $noHp): string
    {
        $digits = preg_replace('/\D+/', '', $noHp) ?? '';

        if (str_starts_with($digits, '620')) {
            return substr($digits, 2);
        }

        if (str_starts_with($digits, '62')) {
            return '0'.substr($digits, 2);
        }

        if (str_starts_with($digits, '8')) {
            return '0'.$digits;
        }

        return $digits;
    }
}
