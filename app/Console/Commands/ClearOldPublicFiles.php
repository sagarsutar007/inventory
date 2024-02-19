<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class ClearOldPublicFiles extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:clear-old-public-files';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->clearDirectory('public/storage/exports');
        $this->clearDirectory('public/storage/imports');
    }

    protected function clearDirectory($directory)
    {
        $files = Storage::files($directory);
        foreach ($files as $file) {
            $fileCreationDate = Carbon::createFromTimestamp(Storage::lastModified($file));
            $diffInDays = $fileCreationDate->diffInDays(now());

            if ($diffInDays > 2) {
                Storage::delete($file);
                $this->info('Deleted old file: ' . $file);
            }
        }
    }
}
