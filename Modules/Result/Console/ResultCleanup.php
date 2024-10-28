<?php

namespace Modules\Result\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class ResultCleanup extends Command
{
    protected $signature = 'result:cleanup';
    protected $description = 'Deletes result PDF files older than a week';

    public function handle()
    {
        $directory = 'student/timeline';
        $files = Storage::files($directory);

        foreach ($files as $file) {
            $lastModified = Storage::lastModified($file);
            if (Carbon::createFromTimestamp($lastModified)->lt(now()->subWeek())) {
                Storage::delete($file);
            }
        }

        $this->info('Old result files cleaned up.');
    }
}

