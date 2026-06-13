<?php

namespace App\Exports;

use App\Models\Receivable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class ReceivablesExport implements FromCollection, WithHeadings, WithMapping
{
    protected array $filters;

    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
    }

    public function collection()
    {
        $query = Receivable::with('receivablePayments');

        if (!empty($this->filters['status'])) {
            $query->where('status', $this->filters['status']);
        }
        if (!empty($this->filters['date_from'])) {
            $query->whereDate('date', '>=', $this->filters['date_from']);
        }
        if (!empty($this->filters['date_to'])) {
            $query->whereDate('date', '<=', $this->filters['date_to']);
        }
        if (!empty($this->filters['search'])) {
            $s = $this->filters['search'];
            $query->where(function ($q) use ($s) {
                $q->where('name', 'like', "%{$s}%")
                  ->orWhere('phone', 'like', "%{$s}%");
            });
        }

        return $query->latest()->get();
    }

    public function headings(): array
    {
        return ['Tanggal', 'Nama', 'No. HP', 'Total', 'Jatuh Tempo', 'Sisa', 'Status'];
    }

    public function map($row): array
    {
        return [
            $row->date->format('d/m/Y'),
            $row->name,
            $row->phone ?? '-',
            $row->amount,
            $row->due_date->format('d/m/Y'),
            $row->remaining,
            $row->status === 'paid' ? 'Lunas' : 'Belum',
        ];
    }
}
