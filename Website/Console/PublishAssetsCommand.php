<?php

namespace Modules\Website\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class PublishAssetsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'website:publish-assets {--force : Overwrite existing files}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Publish Website module assets to public directory';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('Publishing Website Module Assets...');

        $force = $this->option('force');

        // Publish general assets
        $this->call('vendor:publish', [
            '--tag' => 'website-assets',
            '--force' => $force
        ]);

        // Publish PWA files
        $this->call('vendor:publish', [
            '--tag' => 'website-pwa',
            '--force' => $force
        ]);

        $this->info('Website assets published successfully!');
        
        // Show deployment instructions
        $this->showDeploymentInstructions();

        return Command::SUCCESS;
    }

    /**
     * Show deployment instructions
     */
    private function showDeploymentInstructions()
    {
        $this->info('');
        $this->info('🎉 Deployment completed!');
        $this->info('');
        $this->info('📁 Assets have been published to:');
        $this->line('   • CSS files: public/css/');
        $this->line('   • JavaScript files: public/js/');
        $this->line('   • Images: public/images/');
        $this->line('   • Fonts: public/fonts/');
        $this->line('   • PWA Manifest: public/manifest.json');
        $this->line('   • Service Worker: public/sw.js');
        $this->info('');
        $this->info('🚀 You can now:');
        $this->line('   1. Access the website frontend');
        $this->line('   2. Install the Progressive Web App');
        $this->line('   3. Use offline functionality');
        $this->info('');
        $this->info('💡 Commands available:');
        $this->line('   • Republish: php artisan website:publish-assets --force');
        $this->line('   • Publish all: php artisan vendor:publish --tag=website-all --force');
    }
}