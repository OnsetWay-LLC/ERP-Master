<?php

namespace App\Services\Database;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use RuntimeException;

class DatabaseManagementService
{
    private string $backupPath;

   public function __construct()
{
    $this->backupPath = 'C:\\SqlBackups';
}
 public function createBackup(): array
{
    $database = config('database.connections.sqlsrv.database');
    $host = config('database.connections.sqlsrv.host'); // localhost\SQLEXPRESS
    $username = config('database.connections.sqlsrv.username'); // sa
    $password = config('database.connections.sqlsrv.password');

    $fileName = $database . '_' . now()->format('Ymd_His') . '.bak';
    $fullPath = 'C:\\SqlBackups\\' . $fileName;

    $query = "BACKUP DATABASE [$database] TO DISK = N'$fullPath' WITH INIT";

    $command = 'sqlcmd'
        . ' -S "' . $host . '"'
        . ' -U "' . $username . '"'
        . ' -P "' . $password . '"'
        . ' -Q "' . $query . '"';

    exec($command, $output, $exitCode);

    if ($exitCode !== 0) {
        throw new RuntimeException(
            'Backup command failed: ' . implode("\n", $output)
        );
    }

    clearstatcache();

    if (! file_exists($fullPath)) {
        throw new RuntimeException('Backup file was not created at: ' . $fullPath);
    }

    return [
        'database' => $database,
        'file_name' => $fileName,
        'full_path' => $fullPath,
        'size' => $this->formatSize(filesize($fullPath)),
        'created_at' => now()->toDateTimeString(),
    ];
}
    public function listBackups(): array
    {
        $files = File::files($this->backupPath);

        return collect($files)
            ->filter(fn ($file) => $file->getExtension() === 'bak')
            ->map(fn ($file) => [
                'file_name' => $file->getFilename(),
                'size' => $this->formatSize($file->getSize()),
                'created_at' => date('Y-m-d H:i:s', $file->getMTime()),
            ])
            ->sortByDesc('created_at')
            ->values()
            ->toArray();
    }

    public function getBackupPath(string $fileName): string
    {
        $fileName = basename($fileName);
        $fullPath = $this->backupPath . DIRECTORY_SEPARATOR . $fileName;

        if (! File::exists($fullPath)) {
            throw new RuntimeException('Backup file not found.');
        }

        if (pathinfo($fullPath, PATHINFO_EXTENSION) !== 'bak') {
            throw new RuntimeException('Invalid backup file.');
        }

        return $fullPath;
    }

    public function deleteBackup(string $fileName): void
    {
        $fullPath = $this->getBackupPath($fileName);

        File::delete($fullPath);
    }

    private function formatSize(int $bytes): string
    {
        if ($bytes >= 1073741824) {
            return round($bytes / 1073741824, 2) . ' GB';
        }

        if ($bytes >= 1048576) {
            return round($bytes / 1048576, 2) . ' MB';
        }

        if ($bytes >= 1024) {
            return round($bytes / 1024, 2) . ' KB';
        }

        return $bytes . ' B';
    }
    public function restoreDatabase(string $backupFile, ?string $databaseName = null): array
{
    $database = $databaseName
        ?? config('database.connections.sqlsrv.database');

    $backupPath = $this->backupPath . DIRECTORY_SEPARATOR . basename($backupFile);

    if (! file_exists($backupPath)) {
        throw new RuntimeException('Backup file not found.');
    }

    $host = config('database.connections.sqlsrv.host');
    $username = config('database.connections.sqlsrv.username');
    $password = config('database.connections.sqlsrv.password');

   $query = "ALTER DATABASE [$database] SET SINGLE_USER WITH ROLLBACK IMMEDIATE; RESTORE DATABASE [$database] FROM DISK = N'$backupPath' WITH REPLACE; ALTER DATABASE [$database] SET MULTI_USER;";

$command =
    'sqlcmd'
    . ' -S "' . $host . '"'
    . ' -U "' . $username . '"'
    . ' -P "' . $password . '"'
    . ' -Q "' . $query . '"';

exec($command . ' 2>&1', $output, $exitCode);

    if ($exitCode !== 0) {
    throw new RuntimeException(
        'Restore command failed: ' . implode("\n", $output)
    );
}

    return [
        'database' => $database,
        'backup_file' => basename($backupFile),
        'restored_at' => now()->toDateTimeString(),
    ];
}
}