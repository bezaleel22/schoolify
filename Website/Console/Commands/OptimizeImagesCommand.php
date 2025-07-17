<?php

namespace Modules\Website\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
// Note: This is a placeholder for image optimization
// In production, integrate with your preferred image processing library
use Modules\Website\Entities\GalleryImage;
use Modules\Website\Entities\BlogPost;
use Modules\Website\Entities\Event;
use Modules\Website\Entities\WebsitePage;

class OptimizeImagesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'website:optimize-images 
                            {--path= : Specific path to optimize}
                            {--force : Force re-optimization of already optimized images}
                            {--quality=85 : JPEG quality (1-100)}
                            {--max-width=1920 : Maximum width in pixels}
                            {--max-height=1080 : Maximum height in pixels}
                            {--webp : Generate WebP versions}
                            {--progressive : Create progressive JPEGs}
                            {--backup : Create backup of original images}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Optimize images for better web performance';

    /**
     * Image statistics
     */
    protected $stats = [
        'processed' => 0,
        'optimized' => 0,
        'skipped' => 0,
        'errors' => 0,
        'original_size' => 0,
        'optimized_size' => 0,
    ];

    /**
     * Supported image formats
     */
    protected $supportedFormats = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting image optimization...');

        // Get configuration
        $quality = (int) $this->option('quality');
        $maxWidth = (int) $this->option('max-width');
        $maxHeight = (int) $this->option('max-height');
        $generateWebP = $this->option('webp');
        $progressive = $this->option('progressive');
        $createBackup = $this->option('backup');
        $force = $this->option('force');

        // Validate options
        if ($quality < 1 || $quality > 100) {
            $this->error('Quality must be between 1 and 100');
            return 1;
        }

        try {
            if ($this->option('path')) {
                // Optimize specific path
                $this->optimizeDirectory($this->option('path'), $quality, $maxWidth, $maxHeight, $generateWebP, $progressive, $createBackup, $force);
            } else {
                // Optimize all website images
                $this->optimizeAllImages($quality, $maxWidth, $maxHeight, $generateWebP, $progressive, $createBackup, $force);
            }

            $this->displayResults();
            return 0;
        } catch (\Exception $e) {
            $this->error('Optimization failed: ' . $e->getMessage());
            Log::error('Image optimization failed', ['error' => $e->getMessage()]);
            return 1;
        }
    }

    /**
     * Optimize all website images
     */
    protected function optimizeAllImages(int $quality, int $maxWidth, int $maxHeight, bool $generateWebP, bool $progressive, bool $createBackup, bool $force): void
    {
        $this->info('Optimizing all website images...');

        // Define image directories
        $directories = [
            'uploads/website/blog',
            'uploads/website/events',
            'uploads/website/gallery',
            'uploads/website/staff',
            'uploads/website/pages',
        ];

        foreach ($directories as $directory) {
            if (Storage::disk('public')->exists($directory)) {
                $this->optimizeDirectory($directory, $quality, $maxWidth, $maxHeight, $generateWebP, $progressive, $createBackup, $force);
            }
        }

        // Update database records
        $this->updateDatabaseRecords($generateWebP);
    }

    /**
     * Optimize images in a specific directory
     */
    protected function optimizeDirectory(string $directory, int $quality, int $maxWidth, int $maxHeight, bool $generateWebP, bool $progressive, bool $createBackup, bool $force): void
    {
        $this->info("Optimizing directory: {$directory}");

        $files = Storage::disk('public')->allFiles($directory);
        $imageFiles = $this->filterImageFiles($files);

        if (empty($imageFiles)) {
            $this->warn("No images found in {$directory}");
            return;
        }

        $progressBar = $this->output->createProgressBar(count($imageFiles));
        $progressBar->start();

        foreach ($imageFiles as $file) {
            $this->optimizeImage($file, $quality, $maxWidth, $maxHeight, $generateWebP, $progressive, $createBackup, $force);
            $progressBar->advance();
        }

        $progressBar->finish();
        $this->line('');
    }

    /**
     * Filter files to get only supported image formats
     */
    protected function filterImageFiles(array $files): array
    {
        return array_filter($files, function ($file) {
            $extension = strtolower(pathinfo($file, PATHINFO_EXTENSION));
            return in_array($extension, $this->supportedFormats);
        });
    }

    /**
     * Optimize a single image
     */
    protected function optimizeImage(string $filePath, int $quality, int $maxWidth, int $maxHeight, bool $generateWebP, bool $progressive, bool $createBackup, bool $force): void
    {
        try {
            $this->stats['processed']++;

            $fullPath = storage_path('app/public/' . $filePath);
            
            if (!file_exists($fullPath)) {
                $this->stats['skipped']++;
                return;
            }

            // Check if already optimized (unless forced)
            if (!$force && $this->isAlreadyOptimized($fullPath)) {
                $this->stats['skipped']++;
                return;
            }

            // Get original file size
            $originalSize = filesize($fullPath);
            $this->stats['original_size'] += $originalSize;

            // Create backup if requested
            if ($createBackup) {
                $this->createBackup($filePath);
            }

            // Basic image optimization using GD library
            $imageInfo = getimagesize($fullPath);
            if (!$imageInfo) {
                $this->stats['errors']++;
                return;
            }

            $originalWidth = $imageInfo[0];
            $originalHeight = $imageInfo[1];
            $imageType = $imageInfo[2];

            // Calculate new dimensions if resizing needed
            $needsResize = $originalWidth > $maxWidth || $originalHeight > $maxHeight;
            if ($needsResize) {
                $ratio = min($maxWidth / $originalWidth, $maxHeight / $originalHeight);
                $newWidth = (int) ($originalWidth * $ratio);
                $newHeight = (int) ($originalHeight * $ratio);
            } else {
                $newWidth = $originalWidth;
                $newHeight = $originalHeight;
            }

            // Create image resource based on type
            $sourceImage = null;
            switch ($imageType) {
                case IMAGETYPE_JPEG:
                    $sourceImage = imagecreatefromjpeg($fullPath);
                    break;
                case IMAGETYPE_PNG:
                    $sourceImage = imagecreatefrompng($fullPath);
                    break;
                case IMAGETYPE_GIF:
                    $sourceImage = imagecreatefromgif($fullPath);
                    break;
                default:
                    $this->stats['skipped']++;
                    return;
            }

            if (!$sourceImage) {
                $this->stats['errors']++;
                return;
            }

            // Create new image if resizing
            if ($needsResize) {
                $newImage = imagecreatetruecolor($newWidth, $newHeight);
                
                // Preserve transparency for PNG and GIF
                if ($imageType == IMAGETYPE_PNG || $imageType == IMAGETYPE_GIF) {
                    imagealphablending($newImage, false);
                    imagesavealpha($newImage, true);
                    $transparent = imagecolorallocatealpha($newImage, 255, 255, 255, 127);
                    imagefilledrectangle($newImage, 0, 0, $newWidth, $newHeight, $transparent);
                }
                
                imagecopyresampled($newImage, $sourceImage, 0, 0, 0, 0, $newWidth, $newHeight, $originalWidth, $originalHeight);
                imagedestroy($sourceImage);
                $sourceImage = $newImage;
            }

            // Save optimized image
            switch ($imageType) {
                case IMAGETYPE_JPEG:
                    imagejpeg($sourceImage, $fullPath, $quality);
                    break;
                case IMAGETYPE_PNG:
                    imagepng($sourceImage, $fullPath, (int) (9 - ($quality / 100 * 9)));
                    break;
                case IMAGETYPE_GIF:
                    imagegif($sourceImage, $fullPath);
                    break;
            }

            imagedestroy($sourceImage);

            // Generate WebP version if requested and not already WebP
            $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
            if ($generateWebP && $extension !== 'webp') {
                $this->generateWebPVersion($filePath, $fullPath, $quality);
            }

            // Get optimized file size
            $optimizedSize = filesize($fullPath);
            $this->stats['optimized_size'] += $optimizedSize;
            $this->stats['optimized']++;

            // Mark as optimized
            $this->markAsOptimized($fullPath);

        } catch (\Exception $e) {
            $this->stats['errors']++;
            Log::warning('Failed to optimize image', [
                'file' => $filePath,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Check if image is already optimized
     */
    protected function isAlreadyOptimized(string $fullPath): bool
    {
        // Check for optimization marker file
        $markerFile = $fullPath . '.optimized';
        return file_exists($markerFile);
    }

    /**
     * Mark image as optimized
     */
    protected function markAsOptimized(string $fullPath): void
    {
        $markerFile = $fullPath . '.optimized';
        file_put_contents($markerFile, date('Y-m-d H:i:s'));
    }

    /**
     * Create backup of original image
     */
    protected function createBackup(string $filePath): void
    {
        $backupDir = 'backups/images/' . date('Y-m-d');
        $backupPath = $backupDir . '/' . $filePath;
        
        Storage::disk('local')->makeDirectory(dirname($backupPath));
        Storage::disk('local')->copy('public/' . $filePath, $backupPath);
    }

    /**
     * Generate WebP version of image
     */
    protected function generateWebPVersion(string $originalPath, $image, int $quality): void
    {
        try {
            $webpPath = $this->getWebPPath($originalPath);
            $fullWebPPath = storage_path('app/public/' . $webpPath);
            
            // Create WebP version
            $webpImage = clone $image;
            $webpImage->toWebp($quality)->save($fullWebPPath);
            
            // Mark WebP as optimized
            $this->markAsOptimized($fullWebPPath);
            
        } catch (\Exception $e) {
            Log::warning('Failed to generate WebP version', [
                'file' => $originalPath,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Get WebP file path
     */
    protected function getWebPPath(string $originalPath): string
    {
        $pathInfo = pathinfo($originalPath);
        return $pathInfo['dirname'] . '/' . $pathInfo['filename'] . '.webp';
    }

    /**
     * Update database records to include WebP versions
     */
    protected function updateDatabaseRecords(bool $generateWebP): void
    {
        if (!$generateWebP) {
            return;
        }

        $this->info('Updating database records for WebP support...');

        // Update gallery images
        $this->updateGalleryImages();
        
        // Update blog post images
        $this->updateBlogPostImages();
        
        // Update event images
        $this->updateEventImages();
        
        // Update page images
        $this->updatePageImages();
    }

    /**
     * Update gallery image records
     */
    protected function updateGalleryImages(): void
    {
        GalleryImage::chunk(100, function ($images) {
            foreach ($images as $image) {
                $webpPath = $this->getWebPPath($image->image_path);
                if (Storage::disk('public')->exists($webpPath)) {
                    $image->update(['webp_path' => $webpPath]);
                }
            }
        });
    }

    /**
     * Update blog post images
     */
    protected function updateBlogPostImages(): void
    {
        BlogPost::whereNotNull('featured_image')->chunk(100, function ($posts) {
            foreach ($posts as $post) {
                $webpPath = $this->getWebPPath($post->featured_image);
                if (Storage::disk('public')->exists($webpPath)) {
                    $post->update(['featured_image_webp' => $webpPath]);
                }
            }
        });
    }

    /**
     * Update event images
     */
    protected function updateEventImages(): void
    {
        Event::whereNotNull('featured_image')->chunk(100, function ($events) {
            foreach ($events as $event) {
                $webpPath = $this->getWebPPath($event->featured_image);
                if (Storage::disk('public')->exists($webpPath)) {
                    $event->update(['featured_image_webp' => $webpPath]);
                }
            }
        });
    }

    /**
     * Update page images
     */
    protected function updatePageImages(): void
    {
        WebsitePage::whereNotNull('featured_image')->chunk(100, function ($pages) {
            foreach ($pages as $page) {
                $webpPath = $this->getWebPPath($page->featured_image);
                if (Storage::disk('public')->exists($webpPath)) {
                    $page->update(['featured_image_webp' => $webpPath]);
                }
            }
        });
    }

    /**
     * Display optimization results
     */
    protected function displayResults(): void
    {
        $this->line('');
        $this->info('Image Optimization Complete!');
        $this->line('');
        
        $this->table(
            ['Metric', 'Value'],
            [
                ['Images Processed', $this->stats['processed']],
                ['Images Optimized', $this->stats['optimized']],
                ['Images Skipped', $this->stats['skipped']],
                ['Errors', $this->stats['errors']],
                ['Original Size', $this->formatBytes($this->stats['original_size'])],
                ['Optimized Size', $this->formatBytes($this->stats['optimized_size'])],
                ['Space Saved', $this->formatBytes($this->stats['original_size'] - $this->stats['optimized_size'])],
                ['Reduction', $this->calculateReduction()],
            ]
        );
        
        if ($this->stats['errors'] > 0) {
            $this->warn("Some images could not be optimized. Check the logs for details.");
        }
    }

    /**
     * Calculate size reduction percentage
     */
    protected function calculateReduction(): string
    {
        if ($this->stats['original_size'] === 0) {
            return '0%';
        }
        
        $reduction = (($this->stats['original_size'] - $this->stats['optimized_size']) / $this->stats['original_size']) * 100;
        return number_format($reduction, 1) . '%';
    }

    /**
     * Format bytes to human readable format
     */
    protected function formatBytes(int $bytes): string
    {
        if ($bytes === 0) {
            return '0 B';
        }
        
        $units = ['B', 'KB', 'MB', 'GB'];
        $i = floor(log($bytes, 1024));
        
        return round($bytes / (1024 ** $i), 2) . ' ' . $units[$i];
    }

    /**
     * Clean up optimization markers
     */
    public function cleanupMarkers(): void
    {
        $this->info('Cleaning up optimization markers...');
        
        $directories = [
            'uploads/website/blog',
            'uploads/website/events',
            'uploads/website/gallery',
            'uploads/website/staff',
            'uploads/website/pages',
        ];

        foreach ($directories as $directory) {
            if (Storage::disk('public')->exists($directory)) {
                $files = Storage::disk('public')->allFiles($directory);
                
                foreach ($files as $file) {
                    if (str_ends_with($file, '.optimized')) {
                        Storage::disk('public')->delete($file);
                    }
                }
            }
        }
        
        $this->info('Optimization markers cleaned up.');
    }
}