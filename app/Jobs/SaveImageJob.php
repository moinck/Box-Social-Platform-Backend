<?php

namespace App\Jobs;

use App\Models\ImageStockManagement;
use Exception;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class SaveImageJob implements ShouldQueue
{
    use Queueable;

    public $val;

    /**
     * Create a new job instance.
     */
    public function __construct(ImageStockManagement $val)
    {
        $this->val = $val;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {

            $response = Http::get($this->val->image_url);
        
            if ($response->successful()) {
                $filename = 'stock-management-images/' . uniqid() . '.jpg';

                Storage::disk('public')->put($filename, $response->body(), 'public');
                
                $this->val->update([
                    'image_url' => $filename
                ]);
            }

            info('Job Run Successfully.');

        } catch (Exception $e) {
            Log::error($e);
        }
    }
}
