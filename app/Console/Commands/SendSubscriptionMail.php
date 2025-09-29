<?php

namespace App\Console\Commands;

use App\Helpers\Helpers;
use App\Jobs\SendDynamicMailJob;
use App\Models\EmailContent;
use App\Models\UserSubscription;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SendSubscriptionMail extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'subscriptions:send-subscription-mail';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Notify users via email regarding the status of their subscription payment, whether it was successful or failed.';

    /**
     * Execute the console command.
     */
    public function handle()
    {

        $currentTime = Carbon::now();

        $subscription = UserSubscription::with(['user','plan'])
            ->whereIn('status',['active','failed']) 
            ->where('plan_id','!=',1)
            ->where('is_mail_send',0)
            ->get();

        // Log::info('Send Subscription Email: CRON Start');

        
        foreach ($subscription as $val) {
            
            $webhookTime = !empty($val->webhook_called_at) ? Carbon::parse($val->webhook_called_at) : null;
            $createdTime = Carbon::parse($val->created_at);

            $isEligible = false;

            // Check if webhook_called_at is older than 20 min, else fallback to created_at
            if ($webhookTime && $webhookTime->diffInMinutes($currentTime) >= 20) {
                $isEligible = true;
            } elseif ($createdTime->diffInMinutes($currentTime) >= 20) {
                $isEligible = true;
            }

            if ($isEligible) {
                if ($val->status == "active") {
                    $email_setting = EmailContent::where('slug','subscription_payment_success')->first();
                } else {
                    $email_setting = EmailContent::where('slug','subscription_payment_failed')->first();
                }

                if (!empty($email_setting)) {
                    
                    if ($val->status == "active") {

                        $format_content = '
                            <table align="center" width="600" cellpadding="0" cellspacing="0" style="background:#fff; border-radius:8px; box-shadow: 0px 0px 20px 10px rgba(0,0,0,.05); margin-top: 20px;">
                                <tr>
                                    <td align="center" style="padding:30px;">
                                        <div style="width:60px; height:60px; border-radius:50%; background:#28a745; color:#fff; font-size:36px; line-height:60px;">✔</div>
                                        <h2 style="color:#333;">Payment Successful!</h2>
                                        <p style="color:#555; font-size:16px;">Your subscription payment was successful.</p>
                                        <p style="color:#555;">Dear |first_name|, your payment for <b>|plan_title|</b> plan of <b>|plan_amount|</b> has been successfully processed.</p>
                                        <hr style="border:0; border-top:1px solid #ddd; margin:20px 0;">
                                        <p style="color:#666; font-size:14px;">Subscription Start Date: <b>|start_date|</b><br>Subscription End Date: <b>|end_date|</b></p>
                                        <p style="margin-top:30px; color:#555;">For support, contact us at |support_email|</p>
                                    </td>
                                </tr>
                            </table>';
                        $format_content = str_replace('|first_name|', "<b>{$val->user->first_name}</b>", $format_content);
                        $format_content = str_replace('|plan_title|', $val->plan->name, $format_content);
                        $format_content = str_replace('|plan_amount|', "£".$val->plan->price, $format_content);
                        $format_content = str_replace('|support_email|', "<a href='mailto:help@boxsocials.com'><b>help@boxsocials.com</b></a>", $format_content);
                        $format_content = str_replace('|start_date|', Helpers::dateFormate($val->current_period_start), $format_content);
                        $format_content = str_replace('|end_date|', Helpers::dateFormate($val->current_period_end), $format_content);
                    } else {

                        $format_content = '
                            <table align="center" width="600" cellpadding="0" cellspacing="0" style="background:#fff; border-radius:8px; box-shadow: 0px 0px 20px 10px rgba(0,0,0,.05); margin-top: 20px;">
                                <tr>
                                    <td align="center" style="padding:30px;">
                                        <div style="width:60px; height:60px; border-radius:50%; background:#dc3545; color:#fff; font-size:36px; line-height:60px;">✖</div>
                                        <h2 style="color:#333;">Payment Failed</h2>
                                        <p style="color:#555; font-size:16px;">We couldn’t process your subscription payment.</p>
                                        <p style="color:#555;">Dear <b>|first_name|</b>, your payment was failed on <b>|payment_date_time|</b>.</p>
                                        <hr style="border:0; border-top:1px solid #ddd; margin:20px 0;">
                                        <p style="color:#666; font-size:14px;">Please update your payment details and try again.</p>
                                        <p style="margin-top:30px; color:#555;">For help, contact us at |support_email|</p>
                                    </td>
                                </tr>
                            </table>';

                        $format_content = str_replace('|first_name|', "<b>{$val->user->first_name}</b>", $format_content);
                        $format_content = str_replace('|payment_date_time|', Helpers::dateFormate($val->created_at), $format_content);
                        $format_content = str_replace('|support_email|', "<a href='mailto:help@boxsocials.com'><b>help@boxsocials.com</b></a>", $format_content);
                    }

                    $data = [
                        'name'    => $val->user->first_name,
                        'email'   => $val->user->email,
                        'subject' => $email_setting->subject,
                        'content' => $format_content,
                    ];
                    // Update subscription to mark email as sent
                    $val->is_mail_send = 1;
                    $val->save();

                    // Dispatch email job
                    dispatch(new SendDynamicMailJob($data));

                }

            }
        }

        // Log::info('Send Subscription Email: CRON End');

    }
}
