<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreReceivablePaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'receivable_id' => [
                'required',
                'exists:receivables,id',
            ],
            'account_id' => [
                'required',
                'exists:accounts,id',
            ],
            'amount' => [
                'required',
                'integer',
                'min:1',
            ],
            'date' => [
                'required',
                'date',
                'before_or_equal:today',
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
            'date' => ':attribute harus berupa tanggal yang valid.',
            'before_or_equal' => ':attribute tidak boleh melebihi hari ini.',
        ];
    }

    public function attributes(): array
    {
        return [
            'receivable_id' => 'Piutang',
            'account_id' => 'Akun',
            'amount' => 'Nominal',
            'date' => 'Tanggal',
        ];
    }
}
