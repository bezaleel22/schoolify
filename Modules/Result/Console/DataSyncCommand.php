<?php

namespace Modules\Result\Console;

use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Artisan;
use Carbon\Carbon;

class DataSyncCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'sync:data {action : The action to perform (pull|push|status)}
                                      {--backup : Create backup before operation}
                                      {--dry-run : Show what would be done without executing}
                                      {--force : Force operation without confirmation}
                                      {--db-only : Sync database only}
                                      {--files-only : Sync files only}';

    /**
     * The console command description.
     */
    protected $description = 'Synchronize database and uploads using Adminer web-based database download';

    /**
     * Remote configuration
     */
    protected $remoteConfig = [];

    /**
     * Local configuration
     */
    protected $localConfig = [];

    /**
     * Create a new command instance.
     */
    public function __construct()
    {
        parent::__construct();
        $this->initializeConfiguration();
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $action = $this->argument('action');

        if (!in_array($action, ['pull', 'push', 'status'])) {
            $this->error('Invalid action. Use: pull, push, or status');
            return 1;
        }

        $this->displayHeader($action);

        try {
            $this->validateConfiguration();

            switch ($action) {
                case 'pull':
                    return $this->pullFromRemote();
                case 'push':
                    return $this->pushToRemote();
                case 'status':
                    return $this->showStatus();
            }
        } catch (Exception $e) {
            $this->error("❌ Error: " . $e->getMessage());
            Log::error("DataSync Error: " . $e->getMessage());
            return 1;
        }

        return 0;
    }

    /**
     * Display command header
     */
    protected function displayHeader($action)
    {
        $this->info("╔══════════════════════════════════════════════════════════════╗");
        $this->info("║                    DATA SYNC TOOL                           ║");
        $this->info("╠══════════════════════════════════════════════════════════════╣");
        $this->info("║ Action: " . str_pad(ucfirst($action), 51) . "║");
        $this->info("║ Time: " . str_pad(now()->format('Y-m-d H:i:s'), 53) . "║");
        $this->info("║ Method: Adminer Web Interface (Fast & Reliable)             ║");

        if ($this->option('dry-run')) {
            $this->info("║ Mode: DRY RUN - No changes will be made                     ║");
        }

        $this->info("╚══════════════════════════════════════════════════════════════╝");
        $this->newLine();
    }

    /**
     * Initialize configuration from environment variables
     */
    protected function initializeConfiguration()
    {
        $this->remoteConfig = [
            'ssh_host' => env('REMOTE_SSH_HOST'),
            'ssh_username' => env('REMOTE_SSH_USERNAME'),
            'ssh_password' => env('REMOTE_SSH_PASSWORD'),
            'ssh_key' => env('REMOTE_SSH_KEY_PATH'),
            'uploads_path' => env('REMOTE_BIND_MOUNT_PATH', '/home/beznet/backups/student_uploads'),
        ];

        $this->localConfig = [
            'host' => env('LOCAL_DB_HOST', '127.0.0.1'),
            'port' => env('DB_PORT', '3306'),
            'database' => env('DB_DATABASE'),
            'username' => env('DB_USERNAME'),
            'password' => env('DB_PASSWORD'),
            'uploads_path' => public_path('uploads/student'),
        ];
    }

    /**
     * Validate configuration and connectivity
     */
    protected function validateConfiguration()
    {
        $this->info("🔍 Validating configuration...");

        // Check required environment variables for SSH (file sync)
        if (!$this->option('db-only')) {
            $required = ['REMOTE_SSH_HOST', 'REMOTE_SSH_USERNAME'];
            $missing = [];

            foreach ($required as $key) {
                if (empty(env($key))) {
                    $missing[] = $key;
                }
            }

            if (!empty($missing)) {
                throw new Exception('Missing required environment variables: ' . implode(', ', $missing));
            }

            // Check SSH authentication
            if (empty($this->remoteConfig['ssh_password']) && empty($this->remoteConfig['ssh_key'])) {
                throw new Exception('Either REMOTE_SSH_PASSWORD or REMOTE_SSH_KEY_PATH must be provided');
            }

            // Test SSH connectivity for file sync
            $this->info("🔗 Testing SSH connectivity...");
            if (!$this->testSSHConnection()) {
                throw new Exception('SSH connection failed');
            }
        }

        // Test Adminer connectivity for database sync
        if (!$this->option('files-only')) {
            $this->info("🌐 Testing Adminer web interface connectivity...");
            if (!$this->testAdminerConnectivity()) {
                throw new Exception('Adminer web interface not accessible');
            }
        }

        $this->info("✅ Configuration valid and connectivity confirmed");
        $this->newLine();
    }

    /**
     * Test Adminer connectivity
     */
    protected function testAdminerConnectivity()
    {
        try {
            $exitCode = Artisan::call('adminer:backup', [
                'target' => 'remote',
                '--dry-run' => true,
                '--timeout' => 10
            ]);

            return $exitCode === 0;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Test SSH connection
     */
    protected function testSSHConnection()
    {
        $command = $this->buildSSHCommand("echo 'test'");
        $output = [];
        $returnCode = 0;
        exec($command, $output, $returnCode);
        return $returnCode === 0;
    }

    /**
     * Pull data from remote to local (Morning routine)
     */
    protected function pullFromRemote()
    {
        $this->info("🌅 MORNING ROUTINE - Pulling remote data to local");
        $this->info("Using Adminer web interface for fast, reliable database synchronization.");
        $this->newLine();

        if (!$this->option('force') && !$this->option('dry-run')) {
            if (!$this->confirm('⚠️  This will overwrite your local data. Continue?')) {
                $this->info('Operation cancelled.');
                return 0;
            }
        }

        $success = true;

        // Create backup if requested
        if ($this->option('backup')) {
            $this->info("💾 Creating local backup...");
            if (!$this->option('dry-run')) {
                $this->createLocalBackup();
            }
            $this->info("✅ Backup created");
        }

        // Sync database via Adminer
        if (!$this->option('files-only')) {
            $this->info("🗄️  Synchronizing database via Adminer...");
            $success &= $this->syncDatabaseViaAdminer();
        }

        // Sync files
        if (!$this->option('db-only')) {
            $this->info("📁 Synchronizing files...");
            $success &= $this->syncFiles('pull');
        }

        if ($success) {
            $this->info("✅ Morning sync completed successfully!");
            $this->info("Your local environment is now synchronized with the remote server.");
        } else {
            $this->error("❌ Morning sync completed with errors. Check logs for details.");
        }

        return $success ? 0 : 1;
    }

    /**
     * Push data from local to remote (Evening routine)
     */
    protected function pushToRemote()
    {
        $this->info("🌇 EVENING ROUTINE - Pushing local data to remote");
        $this->newLine();

        $this->warn("⚠️  Database push via Adminer is not supported.");
        $this->warn("    Adminer only supports exporting (downloading) databases, not importing.");
        $this->warn("    For database push operations, use the original workflow:sync command.");
        $this->newLine();

        if (!$this->option('db-only')) {
            $this->info("📁 Synchronizing files only...");
            $success = $this->syncFiles('push');

            if ($success) {
                $this->info("✅ File sync completed successfully!");
                $this->warn("💡 For database push, use: php artisan workflow:sync push");
            } else {
                $this->error("❌ File sync completed with errors.");
            }

            return $success ? 0 : 1;
        } else {
            $this->error("❌ Database-only push not supported via Adminer.");
            $this->info("💡 Use the original command: php artisan workflow:sync push --db-only");
            return 1;
        }
    }

    /**
     * Show synchronization status
     */
    protected function showStatus()
    {
        $this->info("📊 SYNCHRONIZATION STATUS (Adminer Method)");
        $this->newLine();

        // Database status
        $this->info("🗄️  Database Status:");
        $this->info("   📊 Checking latest Adminer downloads...");
        $this->checkAdminerBackups();
        $this->newLine();

        // Files status
        $this->info("📁 Files Status:");
        $this->checkFilesStatus();
        $this->newLine();

        $this->info("💡 Commands:");
        $this->info("   • Pull: php artisan sync:data pull");
        $this->info("   • Push: php artisan sync:data push");
        $this->info("   • Files only: php artisan sync:data pull --files-only");
        $this->info("   • Database only: php artisan sync:data pull --db-only");
        $this->info("   • Backup: php artisan adminer:backup");
        $this->info("   • Test run: php artisan sync:data pull --dry-run");

        return 0;
    }

    /**
     * Synchronize database via Adminer web interface
     */
    protected function syncDatabaseViaAdminer()
    {
        try {
            $backupDir = storage_path('app/backups/adminer');
            File::ensureDirectoryExists($backupDir);

            $this->info("   🌐 Downloading compressed database from Adminer...");

            $options = [
                '--backup-dir' => $backupDir,
                '--timeout' => 600 // 10 minutes timeout for large databases
            ];

            if ($this->option('dry-run')) {
                $options['--dry-run'] = true;
                $this->info("   🔍 Would download database via Adminer web interface");
                return true;
            }

            $exitCode = Artisan::call('adminer:backup', [
                'target' => 'remote',
                '--backup-dir' => $backupDir,
                '--timeout' => 600
            ]);

            if ($exitCode !== 0) {
                throw new Exception('Adminer database download failed');
            }

            $this->info("   📦 Extracting and importing database...");
            return $this->importAdminerDatabase($backupDir);
        } catch (Exception $e) {
            $this->error("   ❌ Adminer database sync failed: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Import database from Adminer download
     */
    protected function importAdminerDatabase($backupDir)
    {
        try {
            // Find the most recent .sql.gz file
            $files = File::glob($backupDir . '/*.sql.gz');

            if (empty($files)) {
                throw new Exception('No .sql.gz files found in backup directory');
            }

            // Sort by modification time, newest first
            usort($files, function ($a, $b) {
                return filemtime($b) - filemtime($a);
            });

            $latestFile = $files[0];
            $this->info("   📁 Using file: " . basename($latestFile));

            // Build mysql import command
            $importCommand = sprintf(
                'gunzip -c %s | mysql -h%s -P%s -u%s -p%s %s',
                escapeshellarg($latestFile),
                escapeshellarg($this->localConfig['host']),
                escapeshellarg($this->localConfig['port']),
                escapeshellarg($this->localConfig['username']),
                escapeshellarg($this->localConfig['password']),
                escapeshellarg($this->localConfig['database'])
            );

            $this->info("   ⏳ Importing database (this may take several minutes)...");

            $output = [];
            $returnCode = 0;
            exec($importCommand, $output, $returnCode);

            if ($returnCode !== 0) {
                throw new Exception("Database import failed with exit code: {$returnCode}");
            }

            $this->info("   ✅ Database imported successfully from Adminer download");

            // Clean up old backup files (keep last 5)
            $this->cleanupOldBackups($backupDir, 5);

            return true;
        } catch (Exception $e) {
            $this->error("   ❌ Database import failed: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Synchronize files
     */
    protected function syncFiles($direction)
    {
        try {
            if ($this->option('dry-run')) {
                $this->info("   Would synchronize files ({$direction})");
                return true;
            }

            $localPath = rtrim($this->localConfig['uploads_path'], '/') . '/';
            $remotePath = rtrim($this->remoteConfig['uploads_path'], '/') . '/';
            $sshUser = $this->remoteConfig['ssh_username'];
            $sshHost = $this->remoteConfig['ssh_host'];

            if ($direction === 'pull') {
                $source = "{$sshUser}@{$sshHost}:{$remotePath}";
                $destination = $localPath;
                $this->info("   📥 Pulling files from remote...");
            } else {
                $source = $localPath;
                $destination = "{$sshUser}@{$sshHost}:{$remotePath}";
                $this->info("   📤 Pushing files to remote...");
            }

            // Ensure local directory exists
            if (!File::exists($localPath)) {
                File::makeDirectory($localPath, 0755, true);
            }

            $rsyncCommand = $this->buildRsyncCommand($source, $destination);
            $this->executeCommand($rsyncCommand, "File synchronization failed");

            $this->info("   ✅ File synchronization completed");
            return true;
        } catch (Exception $e) {
            $this->error("   ❌ File synchronization failed: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Check Adminer backups status
     */
    protected function checkAdminerBackups()
    {
        $backupDir = storage_path('app/backups/adminer');

        if (!File::exists($backupDir)) {
            $this->warn("   ⚠️  No Adminer backups directory found");
            return;
        }

        $files = File::glob($backupDir . '/*.sql.gz');

        if (empty($files)) {
            $this->warn("   ⚠️  No database backups found");
            $this->info("   💡 Run: php artisan adminer:download");
            return;
        }

        // Sort by modification time, newest first
        usort($files, function ($a, $b) {
            return filemtime($b) - filemtime($a);
        });

        $latestFile = $files[0];
        $latestTime = Carbon::createFromTimestamp(filemtime($latestFile));
        $fileSize = File::size($latestFile);

        $this->info("   📊 Latest backup: " . basename($latestFile));
        $this->info("   🕒 Downloaded: " . $latestTime->diffForHumans());
        $this->info("   📦 Size: " . $this->formatBytes($fileSize));
        $this->info("   📁 Total backups: " . count($files));
    }

    /**
     * Check files status
     */
    protected function checkFilesStatus()
    {
        try {
            $localPath = $this->localConfig['uploads_path'];
            $localCount = 0;

            if (File::exists($localPath)) {
                $files = File::allFiles($localPath);
                $localCount = count($files);
            }

            $this->info("   Local files: {$localCount}");

            $remotePath = $this->remoteConfig['uploads_path'];
            $command = $this->buildSSHCommand("find '{$remotePath}' -type f 2>/dev/null | wc -l");
            $output = [];
            exec($command, $output, $returnCode);

            if ($returnCode === 0 && !empty($output)) {
                $remoteCount = intval(trim($output[0]));
                $this->info("   Remote files: {$remoteCount}");

                if ($localCount !== $remoteCount) {
                    $this->warn("   ⚠️  File count differs between local and remote");
                } else {
                    $this->info("   ✅ File count matches");
                }
            } else {
                $this->warn("   ⚠️  Cannot access remote files");
            }
        } catch (Exception $e) {
            $this->error("   ❌ Cannot check files status: " . $e->getMessage());
        }
    }

    /**
     * Clean up old backup files
     */
    protected function cleanupOldBackups($directory, $keepCount = 5)
    {
        $files = File::glob($directory . '/*.sql.gz');

        if (count($files) <= $keepCount) {
            return;
        }

        // Sort by modification time, newest first
        usort($files, function ($a, $b) {
            return filemtime($b) - filemtime($a);
        });

        // Remove old files
        $filesToDelete = array_slice($files, $keepCount);

        foreach ($filesToDelete as $file) {
            File::delete($file);
        }

        $deletedCount = count($filesToDelete);
        $this->info("   🧹 Cleaned up {$deletedCount} old backup files");
    }

    /**
     * Build rsync command
     */
    protected function buildRsyncCommand($source, $destination)
    {
        $options = [
            '-avz',
            '--progress',
            '--partial',
            '--exclude=.DS_Store',
            '--exclude=.gitignore',
            '--exclude=Thumbs.db',
        ];

        $sshOptions = [
            '-o StrictHostKeyChecking=no',
            '-o UserKnownHostsFile=/dev/null',
            '-o LogLevel=ERROR'
        ];

        if ($this->remoteConfig['ssh_password']) {
            $sshCommand = "sshpass -p '{$this->remoteConfig['ssh_password']}' ssh " . implode(' ', $sshOptions);
            $options[] = "-e '{$sshCommand}'";
        } elseif ($this->remoteConfig['ssh_key']) {
            $sshCommand = "ssh -i '{$this->remoteConfig['ssh_key']}' " . implode(' ', $sshOptions);
            $options[] = "-e '{$sshCommand}'";
        }

        return "rsync " . implode(' ', $options) . " '{$source}' '{$destination}'";
    }

    /**
     * Build SSH command
     */
    protected function buildSSHCommand($command)
    {
        $sshHost = $this->remoteConfig['ssh_host'];
        $sshUser = $this->remoteConfig['ssh_username'];
        $sshOptions = '-o StrictHostKeyChecking=no -o UserKnownHostsFile=/dev/null -o LogLevel=ERROR';

        if ($this->remoteConfig['ssh_password']) {
            return "sshpass -p '{$this->remoteConfig['ssh_password']}' ssh {$sshOptions} {$sshUser}@{$sshHost} \"{$command}\"";
        } elseif ($this->remoteConfig['ssh_key']) {
            return "ssh -i '{$this->remoteConfig['ssh_key']}' {$sshOptions} {$sshUser}@{$sshHost} \"{$command}\"";
        } else {
            return "ssh {$sshOptions} {$sshUser}@{$sshHost} \"{$command}\"";
        }
    }

    /**
     * Execute command with error handling
     */
    protected function executeCommand($command, $errorMessage)
    {
        $output = [];
        $returnCode = 0;

        exec($command, $output, $returnCode);

        if ($returnCode !== 0) {
            throw new Exception($errorMessage . " (Exit code: {$returnCode})");
        }

        return $output;
    }

    /**
     * Create local backup
     */
    protected function createLocalBackup()
    {
        $timestamp = now()->format('Y-m-d_H-i-s');
        $backupDir = storage_path('app/backups');
        $backupName = "backup_{$timestamp}";
        $filesBackupDir = "{$backupDir}/{$backupName}_files";

        File::ensureDirectoryExists($backupDir);

        // Backup files only (database will be backed up by Adminer download)
        if (File::exists($this->localConfig['uploads_path'])) {
            File::copyDirectory($this->localConfig['uploads_path'], $filesBackupDir);
            $this->info("   📁 Files backup completed");
        }

        $this->info("   ✅ Backup created: {$backupName}");
    }

    /**
     * Format bytes to human readable format
     */
    protected function formatBytes($bytes, $precision = 2)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, $precision) . ' ' . $units[$i];
    }
}
