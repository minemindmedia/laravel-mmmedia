<?php

namespace Mmmedia\Media\Console\Commands;

use Illuminate\Console\Command;
use Mmmedia\Media\Models\MediaItem;

class GenerateMediaThumbnails extends Command
{
    protected $signature = 'media:generate-thumbnails {--force : Force regeneration of existing thumbnails}';

    protected $description = 'Generate thumbnails for all media items';

    public function handle(): int
    {
        $this->info('Generating thumbnails for media items...');

        $query = MediaItem::where('mime_type', 'like', 'image/%');
        
        if (!$this->option('force')) {
            $query->whereNull('thumbnail_path');
        }

        $mediaItems = $query->get();
        $progressBar = $this->output->createProgressBar($mediaItems->count());

        $generated = 0;
        $failed = 0;

        foreach ($mediaItems as $mediaItem) {
            try {
                $thumbnailUrl = $mediaItem->generateThumbnail();
                if ($thumbnailUrl) {
                    $generated++;
                } else {
                    $failed++;
                }
            } catch (\Exception $e) {
                $this->error("Failed to generate thumbnail for {$mediaItem->original_name}: {$e->getMessage()}");
                $failed++;
            }
            
            $progressBar->advance();
        }

        $progressBar->finish();
        $this->newLine();

        $this->info("Thumbnail generation complete!");
        $this->info("Generated: {$generated}");
        $this->info("Failed: {$failed}");

        return Command::SUCCESS;
    }
}
