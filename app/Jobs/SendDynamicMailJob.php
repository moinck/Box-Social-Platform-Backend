<?php

namespace App\Jobs;

use App\Mail\DynamicContentMail;
use Exception;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendDynamicMailJob implements ShouldQueue
{
    use Queueable;

    protected $data;

    /**
     * Create a new job instance.
     */
    public function __construct($data)
    {
        $this->data = $data;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {

            $data = $this->data;

            Mail::to($data['email'])->send(new DynamicContentMail($data));

        } catch (Exception $e) {
            Log::error($e);
        }
    }
}
