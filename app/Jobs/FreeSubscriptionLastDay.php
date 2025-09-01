<?php

namespace App\Jobs;

use App\Mail\DynamicContentMail;
use App\Mail\TrailSubAfterOctMail;
use App\Models\EmailContent;
use App\Models\UserSubscription;
use Exception;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class FreeSubscriptionLastDay implements ShouldQueue
{
    use Queueable;

    protected UserSubscription $val;
    protected int $type;

    /**
     * Create a new job instance.
     */
    public function __construct(UserSubscription $val, $type)
    {
        $this->val = $val;
        $this->type = $type;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {

            if ($this->type == 1) {
                $slug = 'before_first_oct_mail';
                $placeholder = '|Click here|';
                $link = "<b><a href='" . env('APP_FRONTEND_URL') . "dashboard'>Click here</a></b>";
            } else {
                $slug = 'after_first_oct_mail';
                $placeholder = '|Link to Upgrade to Pro Plan|';
                $link = "<b><a href='" . env('APP_FRONTEND_URL') . "subscription_history'>Link to Upgrade to Pro Plan</a></b>";
            }

            $email_setting = EmailContent::where('slug', $slug)->first();

            if ($email_setting) {
                $format_content = $email_setting->content;
                $format_content = str_replace('|name|', "<b>".$this->val->user->first_name."</b>", $format_content);
                $format_content = str_replace($placeholder, $link, $format_content);

                $data = [
                    'name'    => $this->val->user->name,
                    'email'   => $this->val->user->email,
                    'subject' => $email_setting->subject,
                    'content' => $format_content,
                ];

                Mail::to($this->val->user->email)->send(new DynamicContentMail($data));
            }


        } catch (Exception $e) {
            Log::error($e);
        }
    }
}
