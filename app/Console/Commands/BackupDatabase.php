<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Process\Process;

class BackupDatabase extends Command
{
    protected $signature = 'backup:create
        {--keep=7 : Jumlah backup terakhir yang dipertahankan}
        {--output= : Path folder tujuan backup (default: ~/backups)}';

    protected $description = 'Backup database MySQL ke file SQL';

    public function handle(): int
    {
        $db = config('database.connections.mysql');
        $host = $db['host'];
        $port = $db['port'];
        $database = $db['database'];
        $username = $db['username'];
        $password = $db['password'];

        $homeDir = $_SERVER['HOME'] ?? '/home/u564540896';
        $outputDir = $this->option('output') ?: $homeDir . '/backups';
        if (!is_dir($outputDir)) {
            mkdir($outputDir, 0755, true);
        }

        $filename = 'cash_tracker_' . now()->format('Y-m-d_His') . '.sql';
        $filepath = $outputDir . '/' . $filename;

        $passwordArg = !empty($password)
            ? '--password=' . escapeshellarg($password)
            : '--skip-password';

        $command = sprintf(
            '%s --host=%s --port=%s --user=%s %s --routines --events --single-transaction --quick %s > %s',
            escapeshellarg(env('DB_MYSQLDUMP_PATH', 'mysqldump')),
            escapeshellarg($host),
            escapeshellarg($port),
            escapeshellarg($username),
            $passwordArg,
            escapeshellarg($database),
            escapeshellarg($filepath)
        );

        $this->line('Memulai backup database...');

        $process = Process::fromShellCommandline($command);
        $process->setTimeout(300);
        $process->run();

        if (!$process->isSuccessful()) {
            $this->error('Backup GAGAL: ' . ($process->getErrorOutput() ?: $process->getOutput()));
            return 1;
        }

        $filesize = filesize($filepath);
        $this->info("Backup berhasil: {$filename} (" . round($filesize / 1024 / 1024, 2) . " MB)");

        $keep = (int) $this->option('keep');
        if ($keep > 0) {
            $this->cleanOldBackups($outputDir, $keep);
        }

        return 0;
    }

    protected function cleanOldBackups(string $dir, int $keep): void
    {
        $files = glob($dir . '/cash_tracker_*.sql');
        if (count($files) <= $keep) {
            return;
        }

        sort($files);
        $toDelete = array_slice($files, 0, count($files) - $keep);
        foreach ($toDelete as $file) {
            unlink($file);
            $this->line("  Hapus backup lama: " . basename($file));
        }

        $this->info("Bersihkan " . count($toDelete) . " backup lama (keep={$keep})");
    }
}
