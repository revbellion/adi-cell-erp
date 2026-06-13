<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreRecurringBillRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100'],
            'category' => ['nullable', 'string', 'max:100'],
            'account_id' => ['nullable', 'exists:accounts,id'],
            'amount' => ['required', 'integer', 'min:1'],
            'due_day' => ['required', 'integer', 'between:1,31'],
        ];
    }

    public function messages(): array
    {
        return [
            'required' => ':attribute harus diisi.',
            'integer' => ':attribute harus berupa angka.',
            'min' => ':attribute minimal :min.',
            'max' => ':attribute maksimal :max karakter.',
            'between' => ':attribute harus antara :min dan :max.',
            'exists' => ':attribute tidak valid.',
        ];
    }

    public function attributes(): array
    {
        return [
            'name' => 'Nama Tagihan',
            'category' => 'Kategori',
            'account_id' => 'Akun Default',
            'amount' => 'Nominal',
            'due_day' => 'Tanggal Jatuh Tempo',
        ];
    }
}
