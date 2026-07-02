<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BackupController extends Controller
{
    public function index()
    {
        return view('backups.index');
    }

    public function download()
    {
        $db = config('database.connections.mysql');
        $host = $db['host'];
        $port = $db['port'];
        $database = $db['database'];
        $username = $db['username'];
        $password = $db['password'];

        $mysqlDir = env('DB_MYSQL_DIR', 'C:\laragon\bin\mysql\mysql-8.4.3-winx64\bin');

        $filename = 'cash_tracker_' . now()->format('Y-m-d_His') . '.sql';
        $filepath = storage_path('app/' . $filename);

        $userOption = !empty($password) ? '--password=' . escapeshellarg($password) : '--skip-password';

        $command = sprintf(
            '%s %s --host=%s --port=%s --user=%s %s > %s 2>&1',
            escapeshellarg($mysqlDir . '\mysqldump.exe'),
            $userOption,
            escapeshellarg($host),
            escapeshellarg($port),
            escapeshellarg($username),
            escapeshellarg($database),
            escapeshellarg($filepath)
        );

        exec($command, $output, $exitCode);

        if ($exitCode !== 0) {
            return redirect()->back()->with('error', 'Gagal backup database. Path: ' . $mysqlDir);
        }

        return response()->download($filepath)->deleteFileAfterSend(true);
    }

    public function restore(Request $request)
    {
        $request->validate([
            'backup_file' => 'required|file|mimes:sql,txt|max:102400',
        ]);

        $db = config('database.connections.mysql');
        $host = $db['host'];
        $port = $db['port'];
        $database = $db['database'];
        $username = $db['username'];
        $password = $db['password'];

        $mysqlDir = env('DB_MYSQL_DIR', 'C:\laragon\bin\mysql\mysql-8.4.3-winx64\bin');
        $filepath = $request->file('backup_file')->getRealPath();

        $passArg = !empty($password) ? '--password=' . escapeshellarg($password) : '';

        $command = sprintf(
            '%s --host=%s --port=%s --user=%s %s %s < %s 2>&1',
            escapeshellarg($mysqlDir . '\mysql.exe'),
            escapeshellarg($host),
            escapeshellarg($port),
            escapeshellarg($username),
            $passArg,
            escapeshellarg($database),
            escapeshellarg($filepath)
        );

        exec($command, $output, $exitCode);

        if ($exitCode !== 0) {
            $errorMsg = implode("\n", $output);
            return redirect()->back()->with('error', 'Gagal restore database: ' . $errorMsg);
        }

        return redirect()->back()->with('success', 'Database berhasil direstore.');
    }

    public function resetData(Request $request)
    {
        $request->validate([
            'confirm' => 'required|in:RESET',
        ]);

        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        try {
            DB::transaction(function () {
                DB::table('incomes')->truncate();
                DB::table('expenses')->truncate();
                DB::table('mutations')->truncate();
                DB::table('receivable_payments')->truncate();
                DB::table('receivables')->truncate();
                DB::table('opening_balances')->truncate();
                DB::table('pending_transactions')->truncate();
                DB::table('opname_saldo')->truncate();
                DB::table('stock_transactions')->truncate();
                DB::table('bill_payments')->truncate();
                DB::table('cash_counter_sessions')->truncate();
            });
        } finally {
            DB::statement('SET FOREIGN_KEY_CHECKS=1');
        }

        return redirect()->route('backups.index')->with('success', 'Semua data berhasil direset. Struktur akun tetap aman.');
    }
}
