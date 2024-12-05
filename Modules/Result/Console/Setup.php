<?php

namespace Modules\Result\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Intervention\Image\Facades\Image;

class Setup extends Command
{

    protected $name = 'app:setup';

    public function handle()
    {
        $ac = env('ACCES_CODE', '');
        $email = env('ADMIN_EMAIL', 'onosbrown.saved@gmail.com');
        $password = env('ADMIN_PASSWORD', '#1414bruno#');

        Storage::put('.app_installed', $ac);
        Storage::put('.user_email', $email);
        Storage::put('.user_pass', $password);
        // Storage::makeDirectory('framework/cache/data');

        $this->info('Setup completed');

        $sourceDirectory = public_path('uploads/student');
        $destinationDirectory = public_path('uploads/optimized');

        if (!File::isDirectory($sourceDirectory)) {
            $this->error("Source directory $sourceDirectory does not exist.");
            return;
        }

        // Create destination directory if it doesn't exist
        if (!File::exists($destinationDirectory)) {
            File::makeDirectory($destinationDirectory, 0755, true);
            $this->info("Created destination directory: $destinationDirectory");
        }

        $files = File::allFiles($sourceDirectory);
        foreach ($files as $file) {
            $extension = strtolower($file->getExtension());

            // Skip unsupported file types
            if (!in_array($extension, ['jpg', 'jpeg'])) {
                continue;
            }

            $fileSizeBytes = filesize($file->getPathname());
            $fileSizeKb = $fileSizeBytes / 1024;
            if ($fileSizeKb > 50) {
                $this->optimizeImage($file->getPathname(), $destinationDirectory, $fileSizeKb);
            }
        }

        $this->info('Image optimization completed.');
    }

    /**
     * Optimize the given image file.
     */
    private function optimizeImage(string $filePath, string $destinationDirectory)
    {
        try {
            $originalSize = filesize($filePath); // Old size in bytes
            $image = Image::make($filePath);

            // Reduce the quality to retain 90–95% of the original
            $outputQuality = random_int(70, 75);

            // Save the optimized image in the destination directory with the same filename
            $optimizedPath = $destinationDirectory . '/' . basename($filePath);
            $image->save($optimizedPath, 15);

            $optimizedSize = filesize($optimizedPath); // New size in bytes

            // Compute relative path
            $relativePath = 'public/optimized/' . basename($filePath);

            $oldSizeKb = round($originalSize / 1024, 2);
            $newSizeKb = round($optimizedSize / 1024, 2);

            $this->info("Optimized: $relativePath (Old Size: {$oldSizeKb}KB, New Size: {$newSizeKb}KB)");
        } catch (\Exception $e) {
            $this->error("Failed to optimize $filePath: " . $e->getMessage());
            return null;
        }
    }
}
