<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePrintOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'date' => [
                'required',
                'date',
                'before_or_equal:today',
            ],
            'service_type' => [
                'required',
                'string',
                'in:cetak_foto,fotokopi,laminating,print,ketik,browsing',
            ],
            'quantity' => [
                'required',
                'integer',
                'min:1',
            ],
            'price_per_unit' => [
                'required',
                'integer',
                'min:1',
            ],
            'description' => [
                'nullable',
                'string',
                'max:255',
            ],
            'account_id' => [
                'required',
                'exists:accounts,id',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'required' => ':attribute harus diisi.',
            'integer' => ':attribute harus berupa angka.',
            'min' => ':attribute minimal :min.',
            'max' => ':attribute maksimal :max karakter.',
            'date' => ':attribute harus berupa tanggal yang valid.',
            'before_or_equal' => ':attribute tidak boleh melebihi hari ini.',
            'exists' => ':attribute tidak valid.',
            'in' => 'Jenis layanan tidak valid.',
        ];
    }

    public function attributes(): array
    {
        return [
            'date' => 'Tanggal',
            'service_type' => 'Jenis Layanan',
            'quantity' => 'Jumlah',
            'price_per_unit' => 'Harga Satuan',
            'description' => 'Keterangan',
            'account_id' => 'Akun',
        ];
    }
}
