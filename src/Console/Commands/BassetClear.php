<?php

namespace DigitallyHappy\Assets\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Basset Clear command
 *
 * @property object $output
 */
class BassetClear extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'basset:clear';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clear the bassets cache';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle(): void
    {
        $disk = Storage::disk(config('digitallyhappy.assets.disk'));
        $path = config('digitallyhappy.assets.path');
        $pathRelative = Str::of($disk->path($path))->replace(base_path(), '')->replace('\\', '/');

        $this->line("Clearing bassets '$pathRelative'");

        $disk->deleteDirectory($path);
        $disk->makeDirectory($path);

        $this->info('Done');
    }
}
