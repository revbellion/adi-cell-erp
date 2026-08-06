<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\PrintOrder;
use App\Services\PrintOrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * API penerima transaksi dari print-calc (auto-input Penjualan Jasa Cetak).
 * Dipanggil server-to-server (atau via proxy print-calc) dengan token bersama.
 */
class PrintLogApiController extends Controller
{
    private array $katLabels = [
        'hitam'   => 'Hitam Putih',
        'dominan' => 'Warna Dominan',
        'full'    => 'Warna Full',
    ];

    public function __construct(private PrintOrderService $printOrderService) {}

    public function store(Request $request): JsonResponse
    {
        // 1) Token
        $token = $request->header('X-Print-Token', (string) ($request->input('token') ?? ''));
        if (!hash_equals((string) config('printsync.token', ''), $token)) {
            return response()->json(['ok' => false, 'error' => 'Token tidak valid.'], 401);
        }

        $in = $request->json()->all();

        // 2) ID transaksi (ref anti-dobel)
        $id = preg_replace('/[^A-Za-z0-9_-]/', '', (string) ($in['id'] ?? ''));
        if ($id === '' || strlen($id) > 64) {
            return response()->json(['ok' => false, 'error' => 'ID transaksi tidak valid.'], 400);
        }
        if (PrintOrder::where('print_calc_ref', $id)
            ->orWhere('description', 'like', "%[PC-{$id}]%")
            ->exists()) {
            return response()->json(['ok' => false, 'error' => 'Transaksi sudah terkirim sebelumnya.'], 409);
        }

        // 3) Timestamp
        $ts = preg_replace('/[^0-9\-: ]/', '', (string) ($in['ts'] ?? ''));
        if (!preg_match('/^\d{4}-\d{2}-\d{2} [0-2]\d:[0-5]\d:[0-5]\d$/', $ts)) {
            return response()->json(['ok' => false, 'error' => 'Timestamp tidak valid.'], 400);
        }
        $date = substr($ts, 0, 10);
        if ($date > date('Y-m-d')) {
            return response()->json(['ok' => false, 'error' => 'Tanggal tidak boleh melebihi hari ini.'], 400);
        }
        $nama = mb_substr(trim((string) ($in['nama'] ?? '')), 0, 200);
        if ($nama === '') {
            return response()->json(['ok' => false, 'error' => 'Keterangan wajib diisi.'], 422);
        }

        // 4) Items per kategori (qty = lembar cetak, price = harga/lembar tier)
        $items = $in['items'] ?? null;
        if (!is_array($items) || count($items) === 0) {
            return response()->json(['ok' => false, 'error' => 'Tidak ada item print.'], 400);
        }
        $normalized = [];
        $sum = 0;
        foreach ($items as $it) {
            $kat = (string) ($it['kat'] ?? '');
            if (!isset($this->katLabels[$kat])) {
                return response()->json(['ok' => false, 'error' => "Kategori '{$kat}' tidak dikenal."], 400);
            }
            $qty = max(1, (int) ($it['qty'] ?? 0));
            $price = max(1, (int) ($it['price'] ?? 0));
            $normalized[] = ['kat' => $kat, 'qty' => $qty, 'price' => $price];
            $sum += $qty * $price;
        }

        // 5) Konsistensi total (kalau dikirim print-calc)
        $totalSent = (int) ($in['total'] ?? 0);
        if ($totalSent > 0 && $totalSent !== $sum) {
            return response()->json([
                'ok' => false,
                'error' => "Total tidak konsisten ({$sum} vs {$totalSent}).",
            ], 400);
        }

        // 6) Akun kas default
        $account = Account::where('name', (string) config('printsync.default_account', 'Cash'))->first()
            ?? Account::where('type', 'cash')->first();
        if (! $account) {
            return response()->json([
                'ok' => false,
                'error' => 'Akun kas tidak ditemukan. Cek config printsync.default_account.',
            ], 500);
        }

        // 7) Buat Print Order per kategori (PrintOrderService otomatis bikin Income "Jasa Cetak")
        $orderIds = [];
        $breakdown = [];
        foreach ($normalized as $it) {
            $breakdown[] = $it['qty'] . '× ' . $this->katLabels[$it['kat']];
            $order = $this->printOrderService->create([
                'date'           => $date,
                'service_type'   => 'print',
                'quantity'       => $it['qty'],
                'price_per_unit' => $it['price'],
                'description'    => "{$nama} — {$it['qty']} lbr {$this->katLabels[$it['kat']]}",
                'print_calc_ref' => $id,
                'account_id'     => $account->id,
            ]);
            $orderIds[] = $order->id;
        }

        return response()->json([
            'ok'        => true,
            'orders'    => $orderIds,
            'total'     => $sum,
            'breakdown' => implode(' + ', $breakdown),
        ], 201);
    }
}
