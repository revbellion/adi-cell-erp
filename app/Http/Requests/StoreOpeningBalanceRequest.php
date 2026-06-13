<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreOpeningBalanceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'account_id' => [
                'required',
                'exists:accounts,id',
                Rule::unique('opening_balances', 'account_id')
                    ->where('period', $this->period)
                    ->ignore($this->route('opening_balance')),
            ],
            'period' => [
                'required',
                'regex:/^\d{4}-(0[1-9]|1[0-2])$/',
            ],
            'amount' => [
                'required',
                'integer',
                'min:0',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'required' => ':attribute harus diisi.',
            'exists' => ':attribute tidak ditemukan.',
            'integer' => ':attribute harus berupa angka.',
            'min' => ':attribute minimal :min.',
            'unique' => ':attribute sudah digunakan.',
            'regex' => ':attribute tidak valid.',
        ];
    }

    public function attributes(): array
    {
        return [
            'account_id' => 'Akun',
            'period' => 'Periode',
            'amount' => 'Nominal',
        ];
    }
}
