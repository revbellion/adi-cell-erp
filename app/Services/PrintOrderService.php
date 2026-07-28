<?php

namespace App\Services;

use App\Models\PrintOrder;
use App\Models\Income;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class PrintOrderService
{
    private array $serviceTypes = [
        'cetak_foto' => 'Cetak Foto',
        'fotokopi' => 'Fotokopi',
        'laminating' => 'Laminating',
        'print' => 'Print',
        'ketik' => 'Jasa Ketik',
        'browsing' => 'Browsing / Internet',
    ];

    public function create(array $data): PrintOrder
    {
        return DB::transaction(function () use ($data) {
            $data['date'] = Carbon::parse($data['date'])->format('Y-m-d') . ' ' . now()->format('H:i:s');
            $data['total'] = $data['quantity'] * $data['price_per_unit'];

            $serviceLabel = $this->serviceTypes[$data['service_type']] ?? $data['service_type'];

            $income = Income::create([
                'account_id'  => $data['account_id'],
                'category'    => 'Jasa Cetak',
                'amount'      => $data['total'],
                'description' => $data['description']
                    ?: "Jasa {$serviceLabel} - {$data['quantity']} lembar × Rp " . number_format($data['price_per_unit'], 0, ',', '.'),
                'date'        => $data['date'],
            ]);

            $data['income_id'] = $income->id;

            return PrintOrder::create($data);
        });
    }

    public function update(int $id, array $data): PrintOrder
    {
        return DB::transaction(function () use ($id, $data) {
            $printOrder = PrintOrder::findOrFail($id);
            $data['date'] = Carbon::parse($data['date'])->format('Y-m-d') . ' ' . now()->format('H:i:s');
            $data['total'] = $data['quantity'] * $data['price_per_unit'];

            $serviceLabel = $this->serviceTypes[$data['service_type']] ?? $data['service_type'];

            // Update income terkait
            if ($printOrder->income_id) {
                $income = Income::findOrFail($printOrder->income_id);
                $income->update([
                    'account_id'  => $data['account_id'],
                    'amount'      => $data['total'],
                    'description' => $data['description']
                        ?: "Jasa {$serviceLabel} - {$data['quantity']} lembar × Rp " . number_format($data['price_per_unit'], 0, ',', '.'),
                    'date'        => $data['date'],
                ]);
            }

            $printOrder->update($data);
            return $printOrder;
        });
    }

    public function delete(int $id): bool
    {
        return DB::transaction(function () use ($id) {
            $printOrder = PrintOrder::findOrFail($id);

            // Hapus income terkait
            if ($printOrder->income_id) {
                Income::where('id', $printOrder->income_id)->delete();
            }

            return $printOrder->delete();
        });
    }

    public function getAll(array $filters = []): array
    {
        $query = PrintOrder::with('account');

        if (!empty($filters['date_from'])) {
            $query->whereDate('date', '>=', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $query->whereDate('date', '<=', $filters['date_to']);
        }

        if (!empty($filters['service_type'])) {
            $query->where('service_type', $filters['service_type']);
        }

        if (!empty($filters['search'])) {
            $s = addcslashes($filters['search'], '%_');
            $query->where(function ($q) use ($s) {
                $q->where('description', 'like', "%{$s}%")
                  ->orWhere('service_type', 'like', "%{$s}%");
            });
        }

        $totalAmount = (clone $query)->sum('total');
        $totalQty = (clone $query)->sum('quantity');
        $orders = $query->latest()->paginate(20);

        return compact('orders', 'totalAmount', 'totalQty');
    }

    public function getServiceTypes(): array
    {
        return $this->serviceTypes;
    }
}
