<?php

namespace Modules\Result\Console;

use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Carbon\Carbon;

class AdminerBackupCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'adminer:backup {target=both : Backup target (remote|local|both)}
                                           {--backup-dir=./backups : Directory to save database backups}
                                           {--filename= : Custom filename for the backup}
                                           {--dry-run : Show what would be done without executing}
                                           {--timeout=300 : Request timeout in seconds}';

    /**
     * The console command description.
     */
    protected $description = 'Create database backups from remote and/or local using Adminer interfaces';

    /**
     * Remote Adminer configuration
     */
    protected $remoteAdminerConfig = [
        'base_url' => 'https://db.llacademy.ng',
        'server' => 'schoolifydb-yzrofu',
        'username' => 'schoolify',
        'database' => 'schoolifydb',
        'password' => null, // Will be loaded from environment
    ];

    /**
     * Local Adminer configuration
     */
    protected $localAdminerConfig = [
        'base_url' => 'http://localhost:8080',
        'server' => 'mariadb',
        'username' => 'devuser',
        'database' => 'devdb',
        'password' => null, // Will be loaded from environment
    ];

    /**
     * Create a new command instance.
     */
    public function __construct()
    {
        parent::__construct();
        $this->loadPasswords();
    }

    /**
     * Load database passwords from environment
     */
    protected function loadPasswords()
    {
        $this->remoteAdminerConfig['password'] = env('REMOTE_DB_PASSWORD');
        $this->localAdminerConfig['password'] = env('DB_PASSWORD');
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $target = $this->argument('target');
        
        if (!in_array($target, ['remote', 'local', 'both'])) {
            $this->error('Invalid target. Use: remote, local, or both');
            return 1;
        }

        $this->displayHeader($target);

        try {
            if ($this->option('dry-run')) {
                return $this->performDryRun($target);
            }

            return $this->performBackup($target);
        } catch (Exception $e) {
            $this->error("❌ Backup failed: " . $e->getMessage());
            Log::error("Adminer Backup Error: " . $e->getMessage());
            return 1;
        }
    }

    /**
     * Display command header
     */
    protected function displayHeader($target)
    {
        $this->info("╔══════════════════════════════════════════════════════════════╗");
        $this->info("║                 ADMINER BACKUP COMMAND                      ║");
        $this->info("╠══════════════════════════════════════════════════════════════╣");
        $this->info("║ Target: " . str_pad(ucfirst($target), 53) . "║");
        
        if ($target === 'remote' || $target === 'both') {
            $this->info("║ Remote: " . str_pad($this->remoteAdminerConfig['base_url'], 51) . "║");
        }
        
        if ($target === 'local' || $target === 'both') {
            $this->info("║ Local: " . str_pad($this->localAdminerConfig['base_url'], 52) . "║");
        }
        
        $this->info("║ Time: " . str_pad(now()->format('Y-m-d H:i:s'), 53) . "║");
        
        if ($this->option('dry-run')) {
            $this->info("║ Mode: DRY RUN - No backups will be created                  ║");
        }
        
        $this->info("╚══════════════════════════════════════════════════════════════╝");
        $this->newLine();
    }

    /**
     * Perform backup dry run
     */
    protected function performDryRun($target)
    {
        $this->info("🔍 DRY RUN - Analyzing backup operations:");
        $this->newLine();

        $backupDir = $this->option('backup-dir');

        if ($target === 'remote' || $target === 'both') {
            $this->info("📥 Remote Backup:");
            $this->info("   Source: " . $this->remoteAdminerConfig['base_url']);
            $this->info("   Database: " . $this->remoteAdminerConfig['database']);
            $filename = $this->generateFilename('remote');
            $this->info("   Target File: {$backupDir}/{$filename}");
            $this->info("   Estimated Size: ~3-5 MB (compressed)");
        }

        if ($target === 'local' || $target === 'both') {
            $this->info("📤 Local Backup:");
            $this->info("   Source: " . $this->localAdminerConfig['base_url']);
            $this->info("   Database: " . $this->localAdminerConfig['database']);
            $filename = $this->generateFilename('local');
            $this->info("   Target File: {$backupDir}/{$filename}");
            $this->info("   Estimated Size: ~1-3 MB (compressed)");
        }

        $this->newLine();
        $this->info("✅ Dry run completed - no backups were created");
        return 0;
    }

    /**
     * Perform backup operations
     */
    protected function performBackup($target)
    {
        $results = [];

        if ($target === 'remote' || $target === 'both') {
            $this->info("📥 Creating remote database backup...");
            $results['remote'] = $this->backupRemote();
        }

        if ($target === 'local' || $target === 'both') {
            $this->info("📤 Creating local database backup...");
            $results['local'] = $this->backupLocal();
        }

        $this->newLine();
        $this->info("✅ Backup operations completed!");
        
        foreach ($results as $type => $result) {
            if ($result['success']) {
                $size = File::exists($result['file']) ? $this->formatBytes(File::size($result['file'])) : 'Unknown';
                $this->info("   {$type}: ✅ " . basename($result['file']) . " ({$size})");
            } else {
                $this->error("   {$type}: ❌ " . $result['error']);
            }
        }

        return empty(array_filter($results, fn($r) => !$r['success'])) ? 0 : 1;
    }

    /**
     * Backup remote database
     */
    protected function backupRemote()
    {
        try {
            $this->validateRemoteConfiguration();
            $filePath = $this->downloadRemoteDatabase();
            
            return [
                'success' => true,
                'file' => $filePath
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Backup local database
     */
    protected function backupLocal()
    {
        try {
            $this->validateLocalConfiguration();
            $filePath = $this->downloadLocalDatabase();
            
            return [
                'success' => true,
                'file' => $filePath
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Validate remote configuration
     */
    protected function validateRemoteConfiguration()
    {
        if (!$this->remoteAdminerConfig['password']) {
            throw new Exception('REMOTE_DB_PASSWORD environment variable is required');
        }

        // Test connectivity
        $response = Http::timeout(10)->get($this->remoteAdminerConfig['base_url']);
        if (!$response->successful()) {
            throw new Exception('Cannot connect to remote Adminer');
        }
    }

    /**
     * Validate local configuration
     */
    protected function validateLocalConfiguration()
    {
        if (!$this->localAdminerConfig['password']) {
            throw new Exception('DB_PASSWORD environment variable is required');
        }

        // Test connectivity
        try {
            $response = Http::timeout(10)->get($this->localAdminerConfig['base_url']);
            if (!$response->successful()) {
                throw new Exception('Cannot connect to local Adminer');
            }
        } catch (Exception $e) {
            throw new Exception('Local Adminer not accessible. Is it running on localhost:8080?');
        }
    }

    /**
     * Download remote database
     */
    protected function downloadRemoteDatabase()
    {
        // Authenticate
        $sessionCookie = $this->authenticateWithAdminer($this->remoteAdminerConfig);
        
        // Get export form details
        $exportForm = $this->getExportFormDetails($sessionCookie, $this->remoteAdminerConfig);
        
        // Download export
        $exportData = $this->downloadExportWithAuth($sessionCookie, $exportForm['token'], $this->remoteAdminerConfig);

        // Save to file
        $filename = $this->generateFilename('remote');
        $backupDir = $this->option('backup-dir');
        
        if (!File::exists($backupDir)) {
            File::makeDirectory($backupDir, 0755, true);
        }
        
        $fullPath = rtrim($backupDir, '/') . '/' . $filename;
        File::put($fullPath, $exportData);

        return $fullPath;
    }

    /**
     * Download local database
     */
    protected function downloadLocalDatabase()
    {
        // Authenticate
        $sessionCookie = $this->authenticateWithAdminer($this->localAdminerConfig);
        
        // Get export form details
        $exportForm = $this->getExportFormDetails($sessionCookie, $this->localAdminerConfig);
        
        // Download export
        $exportData = $this->downloadExportWithAuth($sessionCookie, $exportForm['token'], $this->localAdminerConfig);

        // Save to file
        $filename = $this->generateFilename('local');
        $backupDir = $this->option('backup-dir');
        
        if (!File::exists($backupDir)) {
            File::makeDirectory($backupDir, 0755, true);
        }
        
        $fullPath = rtrim($backupDir, '/') . '/' . $filename;
        File::put($fullPath, $exportData);

        return $fullPath;
    }

    /**
     * Authenticate with Adminer
     */
    protected function authenticateWithAdminer($config)
    {
        $response = Http::timeout($this->option('timeout') ?? 300)
            ->withOptions(['verify' => false])
            ->withHeaders([
                'User-Agent' => 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36',
                'Content-Type' => 'application/x-www-form-urlencoded',
                'Referer' => $config['base_url']
            ])
            ->asForm()
            ->post($config['base_url'], [
                'auth[driver]' => 'server',
                'auth[server]' => $config['server'],
                'auth[username]' => $config['username'],
                'auth[password]' => $config['password'],
                'auth[db]' => $config['database']
            ]);

        if (!$response->successful()) {
            throw new Exception("Authentication failed with status: " . $response->status());
        }

        // Extract session cookie
        $cookies = $response->cookies();
        foreach ($cookies as $cookie) {
            if ($cookie->getName() === 'adminer_sid') {
                return $cookie->getValue();
            }
        }

        throw new Exception("No session cookie received after authentication");
    }

    /**
     * Get export form details
     */
    protected function getExportFormDetails($sessionCookie, $config)
    {
        $exportUrl = $this->buildExportUrl($config);
        
        $response = Http::timeout($this->option('timeout') ?? 300)
            ->withOptions(['verify' => false])
            ->withHeaders([
                'Cookie' => 'adminer_sid=' . $sessionCookie,
                'User-Agent' => 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36'
            ])
            ->get($exportUrl);

        if (!$response->successful()) {
            throw new Exception("Failed to get export form: " . $response->status());
        }

        $html = $response->body();
        
        // Extract token
        preg_match('/name="token" value="([^"]+)"/', $html, $tokenMatch);
        if (!$tokenMatch) {
            throw new Exception("Could not find token in export form");
        }
        
        return [
            'token' => $tokenMatch[1],
        ];
    }

    /**
     * Download export with authentication
     */
    protected function downloadExportWithAuth($sessionCookie, $token, $config)
    {
        $exportUrl = $this->buildExportUrl($config);
        
        $formData = [
            'output' => 'gz',
            'format' => 'sql',
            'db_style' => '',
            'routines' => '1',
            'events' => '1',
            'table_style' => 'DROP+CREATE',
            'triggers' => '1',
            'data_style' => 'INSERT+UPDATE',
            'token' => $token,
            'databases[]' => $config['database']
        ];

        $response = Http::timeout($this->option('timeout') ?? 300)
            ->withOptions(['verify' => false])
            ->withHeaders([
                'Cookie' => 'adminer_sid=' . $sessionCookie,
                'User-Agent' => 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36',
                'Referer' => $exportUrl
            ])
            ->asForm()
            ->post($exportUrl, $formData);

        if (!$response->successful()) {
            throw new Exception("Export download failed with status: " . $response->status());
        }

        return $response->body();
    }

    /**
     * Build export URL
     */
    protected function buildExportUrl($config)
    {
        $params = http_build_query([
            'server' => $config['server'],
            'username' => $config['username'],
            'dump' => ''
        ]);

        return $config['base_url'] . '/?' . $params;
    }

    /**
     * Generate filename with prefix
     */
    protected function generateFilename($prefix = 'remote')
    {
        if ($this->option('filename')) {
            return $this->option('filename');
        }

        $timestamp = Carbon::now()->format('Y-m-d_H-i-s');
        $database = $prefix === 'local' ? $this->localAdminerConfig['database'] : $this->remoteAdminerConfig['database'];
        return "{$prefix}-{$database}-{$timestamp}.sql.gz";
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
