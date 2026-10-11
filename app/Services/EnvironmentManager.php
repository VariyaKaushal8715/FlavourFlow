<?php

namespace App\Services;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use RuntimeException;

class EnvironmentManager
{
    /**
     * Get the absolute path to the .env file.
     */
    public function getEnvFilePath(): string
    {
        return app()->environmentFilePath();
    }

    /**
     * Get the directory for safe, non-public .env backups.
     */
    public function getBackupDirectory(): string
    {
        $backupDir = storage_path('backups');

        if (! File::isDirectory($backupDir)) {
            File::makeDirectory($backupDir, 0755, true);
            File::put($backupDir.'/.gitignore', "*\n!.gitignore\n");
        }

        return $backupDir;
    }

    /**
     * Create a safe temporary backup of the .env file.
     */
    public function backup(): ?string
    {
        $envPath = $this->getEnvFilePath();

        if (! File::exists($envPath)) {
            return null;
        }

        $backupDir = $this->getBackupDirectory();
        $backupPath = $backupDir.'/.env.backup.'.time().'_'.Str::random(6);

        File::copy($envPath, $backupPath);

        $this->pruneOldBackups();

        return $backupPath;
    }

    /**
     * Safely and atomically update only the specified key-value pairs in .env.
     *
     * @param  array<string, string>  $keyValues
     *
     * @throws RuntimeException
     */
    public function updateEntries(array $keyValues): bool
    {
        $envPath = $this->getEnvFilePath();

        if (! File::exists($envPath)) {
            throw new RuntimeException('The environment file (.env) does not exist.');
        }

        if (! File::isWritable($envPath)) {
            throw new RuntimeException('The environment file (.env) is not writable by the application.');
        }

        $backupPath = $this->backup();

        try {
            $content = File::get($envPath);

            foreach ($keyValues as $key => $value) {
                $formattedValue = $this->formatEnvValue($value);
                $pattern = '/^#?\s*'.preg_quote($key, '/').'=.*$/m';

                if (preg_match($pattern, $content)) {
                    $content = preg_replace($pattern, "{$key}={$formattedValue}", $content, 1);
                } else {
                    $content = rtrim($content)."\n{$key}={$formattedValue}\n";
                }
            }

            // Write atomically using temporary file in protected backups directory
            $tempPath = $this->getBackupDirectory().'/.env.tmp.'.Str::random(12);
            File::put($tempPath, $content);

            if (! File::exists($tempPath) || File::size($tempPath) === 0) {
                throw new RuntimeException('Failed to write temporary environment file.');
            }

            // Copy over the .env file and remove the temporary file
            File::copy($tempPath, $envPath);
            File::delete($tempPath);

            // Synchronize in-memory config and clear/refresh config cache
            $this->refreshConfiguration($keyValues);

            return true;
        } catch (\Throwable $e) {
            // Restore from backup if something went wrong
            if ($backupPath && File::exists($backupPath)) {
                File::copy($backupPath, $envPath);
            }

            throw new RuntimeException('Failed to update environment configuration: '.$e->getMessage(), 0, $e);
        }
    }

    /**
     * Format the environment value safely.
     */
    protected function formatEnvValue(string $value): string
    {
        $value = trim($value);

        if ($value === '') {
            return '';
        }

        if (preg_match('/\s|"|#|\'/', $value)) {
            return '"'.addcslashes($value, '"').'"';
        }

        return $value;
    }

    /**
     * Refresh in-memory config and Laravel config cache.
     *
     * @param  array<string, string>  $keyValues
     */
    public function refreshConfiguration(array $keyValues): void
    {
        // Update runtime in-memory configuration
        if (isset($keyValues['RAZORPAY_KEY_ID'])) {
            config(['services.razorpay.key_id' => $keyValues['RAZORPAY_KEY_ID']]);
        }

        if (isset($keyValues['RAZORPAY_KEY_SECRET'])) {
            config(['services.razorpay.key_secret' => $keyValues['RAZORPAY_KEY_SECRET']]);
        }

        if (isset($keyValues['RAZORPAY_MODE'])) {
            config(['services.razorpay.mode' => $keyValues['RAZORPAY_MODE']]);
        }

        // Refresh configuration cache if cached or clear stale cache
        try {
            if (app()->configurationIsCached()) {
                Artisan::call('config:cache');
            } else {
                Artisan::call('config:clear');
            }
        } catch (\Throwable) {
            // Non-fatal if artisan command fails in restricted sandbox
        }
    }

    /**
     * Keep only the 5 most recent backups.
     */
    protected function pruneOldBackups(): void
    {
        try {
            $backupDir = storage_path('backups');
            $files = File::glob($backupDir.'/.env.backup.*');

            if (count($files) > 5) {
                usort($files, fn ($a, $b) => filemtime($b) <=> filemtime($a));
                foreach (array_slice($files, 5) as $file) {
                    File::delete($file);
                }
            }
        } catch (\Throwable) {
            // Non-fatal prune cleanup
        }
    }
}
