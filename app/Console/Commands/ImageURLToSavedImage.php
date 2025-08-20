<?php

namespace App\Console\Commands;

use App\Jobs\SaveImageJob;
use App\Models\ImageStockManagement;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class ImageURLToSavedImage extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'images:store';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Image URL to Store a Image in Server using chunk';

    /**
     * Execute the console command.
     */
    public function handle()
    {

        ImageStockManagement::whereNotNull('image_url')
            ->chunk(5, function ($data) {
                foreach ($data as $val) {
                    dispatch(new SaveImageJob($val));
                }
            });

        info("Command Run Successfully.");
        
    }
}
