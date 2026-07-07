<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\Process\Process;

class BackupController extends Controller
{
    public function index()
    {
        return view('backups.index');
    }

    protected function getMysqlDumpPath(): string
    {
        // Bisa di-set di .env: DB_MYSQLDUMP_PATH=mysqldump (default: pakai system path)
        return env('DB_MYSQLDUMP_PATH', 'mysqldump');
    }

    protected function getMysqlClientPath(): string
    {
        return env('DB_MYSQL_CLIENT', 'mysql');
    }

    public function download()
    {
        $db = config('database.connections.mysql');
        $host = $db['host'];
        $port = $db['port'];
        $database = $db['database'];
        $username = $db['username'];
        $password = $db['password'];

        $filename = 'cash_tracker_' . now()->format('Y-m-d_His') . '.sql';
        $filepath = storage_path('app/' . $filename);

        $passwordArg = !empty($password) ? '--password=' . escapeshellarg($password) : '--skip-password';

        $command = sprintf(
            '%s --host=%s --port=%s --user=%s %s %s > %s',
            escapeshellarg($this->getMysqlDumpPath()),
            escapeshellarg($host),
            escapeshellarg($port),
            escapeshellarg($username),
            $passwordArg,
            escapeshellarg($database),
            escapeshellarg($filepath)
        );

        $process = Process::fromShellCommandline($command);
        $process->run();

        if (!$process->isSuccessful()) {
            return redirect()->back()->with('error', 'Gagal backup database. Pastikan mysqldump tersedia di server. Jika perlu, set DB_MYSQLDUMP_PATH di .env');
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

        $filepath = $request->file('backup_file')->getRealPath();

        // Strip mysqldump warnings from backup file (e.g. "mysqldump: [Warning] ...")
        // so they don't cause SQL syntax errors during restore
        $cleanPath = $filepath . '.clean';
        file_put_contents($cleanPath, preg_replace(
            '/^mysqldump:\s*\[Warning\].*\n/m',
            '',
            file_get_contents($filepath)
        ));

        $passwordArg = !empty($password) ? '--password=' . escapeshellarg($password) : '';

        $command = sprintf(
            '%s --host=%s --port=%s --user=%s %s %s < %s 2>&1',
            escapeshellarg($this->getMysqlClientPath()),
            escapeshellarg($host),
            escapeshellarg($port),
            escapeshellarg($username),
            $passwordArg,
            escapeshellarg($database),
            escapeshellarg($cleanPath)
        );

        $process = Process::fromShellCommandline($command);
        $process->run();

        @unlink($cleanPath);

        if (!$process->isSuccessful()) {
            $errorMsg = $process->getErrorOutput() ?: $process->getOutput();
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
            });
        } finally {
            DB::statement('SET FOREIGN_KEY_CHECKS=1');
        }

        return redirect()->route('backups.index')->with('success', 'Semua data berhasil direset. Struktur akun tetap aman.');
    }
}
