<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePendingTransactionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'type' => [
                'required',
                'string',
                'in:edc,transfer,tf_masuk',
            ],
            'bank_type' => [
                'nullable',
                'string',
                'in:bca,non_bca',
            ],
            'description' => [
                'required',
                'string',
                'max:255',
            ],
            'amount' => [
                'required',
                'integer',
                'min:1',
            ],
            'pending_date' => [
                'required',
                'date',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'required' => ':attribute harus diisi.',
            'string' => ':attribute harus berupa teks.',
            'integer' => ':attribute harus berupa angka.',
            'min' => ':attribute minimal :min.',
            'in' => ':attribute tidak valid.',
            'date' => ':attribute harus berupa tanggal yang valid.',
        ];
    }

    public function attributes(): array
    {
        return [
            'type' => 'Tipe',
            'bank_type' => 'Tipe Bank',
            'description' => 'Deskripsi',
            'amount' => 'Nominal',
            'pending_date' => 'Tanggal',
        ];
    }
}
