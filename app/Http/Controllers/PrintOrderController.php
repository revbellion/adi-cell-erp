<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePrintOrderRequest;
use App\Http\Requests\UpdatePrintOrderRequest;
use App\Models\Account;
use App\Services\PrintOrderService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PrintOrderController extends Controller
{
    public function __construct(
        protected PrintOrderService $printOrderService
    ) {}

    public function index(Request $request)
    {
        $filters = $this->parseFilters($request);
        $result = $this->printOrderService->getAll($filters);

        return view('print-orders.index', [
            'orders' => $result['orders'],
            'accounts' => Account::active()->where('type', '!=', 'ppob')->get(),
            'serviceTypes' => $this->printOrderService->getServiceTypes(),
            'totalAmount' => $result['totalAmount'],
            'totalQty' => $result['totalQty'],
            'defaultAccount' => Account::active()->where('name', config('accounts.cash_name'))->first(),
        ]);
    }

    public function store(StorePrintOrderRequest $request)
    {
        try {
            $this->printOrderService->create($request->validated());
            return redirect()->back()->with('success', 'Pesanan jasa cetak berhasil dicatat.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal mencatat pesanan: ' . $e->getMessage());
        }
    }

    public function update(UpdatePrintOrderRequest $request, $id)
    {
        try {
            $this->printOrderService->update($id, $request->validated());
            return redirect()->back()->with('success', 'Pesanan jasa cetak berhasil diubah.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal mengubah pesanan: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        try {
            $this->printOrderService->delete($id);
            return redirect()->back()->with('success', 'Pesanan jasa cetak berhasil dihapus.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal menghapus pesanan: ' . $e->getMessage());
        }
    }

    public function bulkDelete(Request $request)
    {
        $request->validate(['ids' => 'required|array']);
        $deleted = 0;
        foreach ($request->ids as $id) {
            try {
                $this->printOrderService->delete($id);
                $deleted++;
            } catch (\Exception $e) {
                // skip
            }
        }
        return redirect()->back()->with('success', "{$deleted} data berhasil dihapus.");
    }

    private function parseFilters(Request $request): array
    {
        $raw = $request->only(['date_from', 'date_to', 'service_type', 'search']);
        $raw = array_map(fn($v) => $v === '' ? null : $v, $raw);

        return array_filter(
            Validator::make($raw, [
                'date_from' => 'nullable|date',
                'date_to' => 'nullable|date',
                'service_type' => 'nullable|string|in:cetak_foto,fotokopi,laminating,print,ketik,browsing',
                'search' => 'nullable|string|max:100',
            ])->valid(),
            fn($v) => $v !== null
        );
    }
}
