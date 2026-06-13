<?php

namespace App\Exports;

use App\Models\Mutation;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class MutationsExport implements FromCollection, WithHeadings, WithMapping
{
    protected array $filters;

    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
    }

    public function collection()
    {
        $query = Mutation::with('fromAccount', 'toAccount');

        if (!empty($this->filters['date_from'])) {
            $query->whereDate('date', '>=', $this->filters['date_from']);
        }
        if (!empty($this->filters['date_to'])) {
            $query->whereDate('date', '<=', $this->filters['date_to']);
        }
        if (!empty($this->filters['search'])) {
            $s = $this->filters['search'];
            $query->where(function ($q) use ($s) {
                $q->where('description', 'like', "%{$s}%");
            });
        }

        return $query->latest()->get();
    }

    public function headings(): array
    {
        return ['Tanggal', 'Dari', 'Ke', 'Nominal', 'Keterangan'];
    }

    public function map($row): array
    {
        return [
            $row->date->format('d/m/Y'),
            $row->fromAccount->name ?? '-',
            $row->toAccount->name ?? '-',
            $row->amount,
            $row->description ?? '-',
        ];
    }
}
