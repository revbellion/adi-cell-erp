<?php

namespace App\Http\Requests;

use App\Models\Account;
use Illuminate\Foundation\Http\FormRequest;

class StoreCashCounterSessionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'account_id' => [
                'nullable',
                'exists:accounts,id',
                function ($attribute, $value, $fail) {
                    if ($value && Account::where('id', $value)->where('type', '!=', 'cash')->exists()) {
                        $fail('Akun harus bertipe Cash.');
                    }
                },
            ],
            'title' => 'required|string|max:255',
            'opening_balance' => 'required|integer|min:0',
            'denominations' => 'required|array',
            'denominations.*' => 'integer|min:0',
            'total_amount' => 'required|integer|min:0',
        ];
    }

    public function messages(): array
    {
        return [
            'required' => ':attribute harus diisi.',
            'integer' => ':attribute harus berupa angka.',
            'min' => ':attribute minimal :min.',
            'max' => ':attribute maksimal :max karakter.',
        ];
    }

    public function attributes(): array
    {
        return [
            'account_id' => 'Akun',
            'title' => 'Judul',
            'opening_balance' => 'Saldo Awal',
            'denominations' => 'Denominasi',
            'total_amount' => 'Total',
        ];
    }
}
