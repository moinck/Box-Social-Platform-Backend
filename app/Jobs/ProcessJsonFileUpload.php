<?php

namespace App\Jobs;

use App\Models\PostTemplate;
use App\Models\UserTemplates;
use Carbon\Carbon;
use Exception;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ProcessJsonFileUpload implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $templateId;
    protected $filePath;
    protected $extension;
    protected $templateType;

    /**
     * Create a new job instance.
     */
    public function __construct($templateId, $filePath, $extension, $templateType)
    {
        $this->templateId = $templateId;
        $this->filePath = $filePath;
        $this->extension = $extension;
        $this->templateType = $templateType; /** 1 => Admin Template, 2 => User Template */
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $appType = config('app.env') ?? env('APP_ENV', 'local');

        if ($this->templateType == 1) {
            $template = PostTemplate::find($this->templateId);
            $userRole = "admin";
        } else if ($this->templateType == 2) {
            $template = UserTemplates::find($this->templateId);
            $userRole = "user";
        }

        if ($template) {
            try {

                $filename = 'template_' . rand(1000, 9999) . '_' . time() . '.' . $this->extension;
                $path     = $appType . '/' .$userRole. '/json/template-data/' . $filename;

                Storage::disk('digitalocean')->put($path,file_get_contents($this->filePath),'public');

                $uploadedUrl = Storage::disk('digitalocean')->url($path);

                if ($uploadedUrl) {
                    $template->template_url = $uploadedUrl;
                    $template->uploaded_at = Carbon::now();
                    $template->is_uploaded = 1;
                    $template->save();
                } else {
                    $template->uploaded_at = Carbon::now();
                    $template->is_uploaded = 2;
                    $template->save();
                }

            } catch (Exception $e) {
                Log::error('Template upload failed for ID: '.$this->templateId . ' - ' . $e->getMessage());

                $template->uploaded_at = Carbon::now();
                $template->is_uploaded = 3;
                $template->save();
            }
        }
    }
}
