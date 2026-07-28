<?php

namespace App\Exports;

use App\Models\PrintOrder;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class PrintOrdersExport implements FromCollection, WithHeadings, WithMapping
{
    protected array $filters;

    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
    }

    public function collection()
    {
        $query = PrintOrder::with('account');

        if (!empty($this->filters['date_from'])) {
            $query->whereDate('date', '>=', $this->filters['date_from']);
        }
        if (!empty($this->filters['date_to'])) {
            $query->whereDate('date', '<=', $this->filters['date_to']);
        }
        if (!empty($this->filters['service_type'])) {
            $query->where('service_type', $this->filters['service_type']);
        }
        if (!empty($this->filters['search'])) {
            $s = addcslashes($this->filters['search'], '%_');
            $query->where(function ($q) use ($s) {
                $q->where('description', 'like', "%{$s}%")
                  ->orWhere('service_type', 'like', "%{$s}%");
            });
        }

        return $query->latest()->get();
    }

    public function headings(): array
    {
        return ['Tanggal', 'Akun', 'Jenis Layanan', 'Jumlah', 'Harga Satuan', 'Total', 'Keterangan'];
    }

    public function map($row): array
    {
        $serviceLabels = [
            'cetak_foto' => 'Cetak Foto',
            'fotokopi' => 'Fotokopi',
            'laminating' => 'Laminating',
            'print' => 'Print',
            'ketik' => 'Jasa Ketik',
            'browsing' => 'Browsing / Internet',
        ];

        return [
            $row->date->format('d/m/Y'),
            $row->account?->name ?? '-',
            $serviceLabels[$row->service_type] ?? $row->service_type,
            $row->quantity,
            $row->price_per_unit,
            $row->total,
            $row->description ?? '-',
        ];
    }
}
