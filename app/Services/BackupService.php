<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Symfony\Component\Process\Process;

/**
 * Wraps `mysqldump`/`mysql` as shell processes rather than reading/writing
 * the database through Eloquent -- a full backup/restore needs to be a
 * faithful byte-for-byte database dump (including indexes, constraints,
 * and every table this system has, plus any future ones), which only the
 * database engine's own dump tool can guarantee. Application-level
 * export/import would silently drift from the real schema over time.
 *
 * Backups are stored on the 'local' disk under backups/, never on a
 * publicly-accessible disk -- a database dump contains password hashes
 * and full customer PII and must never be web-reachable.
 */
class BackupService
{
    public function create(): string
    {
        $connection = config('database.default');
        $config = config("database.connections.{$connection}");

        $filename = 'backups/backup-' . now()->format('Y-m-d-His') . '.sql';
        $fullPath = Storage::disk('local')->path($filename);

        Storage::disk('local')->makeDirectory('backups');

        $process = new Process([
            'mysqldump',
            '--set-gtid-purged=OFF',
            '--host=' . $config['host'],
            '--port=' . ($config['port'] ?? 3306),
            '--user=' . $config['username'],
            '--password=' . $config['password'],
            '--single-transaction', // consistent snapshot without locking tables, safe for a live store
            '--routines',
            '--triggers',
            $config['database'],
        ]);

        $process->setTimeout(300);

        $output = fopen($fullPath, 'w');
        $process->setTimeout(300)->run(function ($type, $buffer) use ($output) {
            if ($type === Process::OUT) {
                fwrite($output, $buffer);
            }
        });
        fclose($output);

        if (!$process->isSuccessful()) {
            @unlink($fullPath);
            throw new RuntimeException('Database backup failed: ' . $process->getErrorOutput());
        }

        return $filename;
    }

    public function listBackups(): array
    {
        if (!Storage::disk('local')->exists('backups')) {
            return [];
        }

        return collect(Storage::disk('local')->files('backups'))
            ->filter(fn($path) => str_ends_with($path, '.sql'))
            ->map(fn($path) => [
                'path' => $path,
                'filename' => basename($path),
                'size_bytes' => Storage::disk('local')->size($path),
                'created_at' => \Carbon\Carbon::createFromTimestamp(Storage::disk('local')->lastModified($path)),
            ])
            ->sortByDesc('created_at')
            ->values()
            ->all();
    }

    /**
     * Restoring is destructive and irreversible (it overwrites live data),
     * so this method is deliberately the only entry point -- there's no
     * "preview" or "dry run" because a SQL dump restore can't meaningfully
     * offer one. The controller calling this MUST require explicit
     * confirmation (see SettingController::restoreBackup) before reaching here.
     */
    public function restore(string $backupFilename): void
    {
        $path = "backups/{$backupFilename}";

        if (!Storage::disk('local')->exists($path)) {
            throw new RuntimeException("Backup file not found: {$backupFilename}");
        }

        $connection = config('database.default');
        $config = config("database.connections.{$connection}");
        $fullPath = Storage::disk('local')->path($path);

        $process = new Process([
            'mysql',
            '--host=' . $config['host'],
            '--port=' . ($config['port'] ?? 3306),
            '--user=' . $config['username'],
            '--password=' . $config['password'],
            $config['database'],
        ]);

        $process->setTimeout(300);

        $input = fopen($fullPath, 'r');
        $process->setInput($input);
        $process->run();
        fclose($input);

        if (!$process->isSuccessful()) {
            throw new RuntimeException('Database restore failed: ' . $process->getErrorOutput());
        }
    }

    public function deleteBackup(string $filename): void
    {
        Storage::disk('local')->delete("backups/{$filename}");
    }
}
