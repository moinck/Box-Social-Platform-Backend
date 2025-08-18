<?php

namespace App\Console\Commands;

use App\Helpers\Helpers;
use App\Models\UserSubscription;
use App\Models\UserDownloads;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class CheckExpiredSubscriptions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    // protected $signature = 'app:check-expired-subscriptions';
    protected $signature = 'subscriptions:check-expired {--dry-run : Show what would be updated without making changes}';


    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check and update expired subscriptions based on current_period_end date';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Checking for expired subscriptions...');
        
        $dryRun = $this->option('dry-run');
        $currentDateTime = Carbon::now();
        
        if ($dryRun) {
            $this->warn('DRY RUN MODE - No changes will be made');
        }
        
        // Find expired subscriptions that haven't been marked as cancelled yet
        $expiredSubscriptions = UserSubscription::where('current_period_end', '<', $currentDateTime)
            // ->whereNotIn('status', ['cancelled', 'canceled']) // Handle both spellings
            // ->whereNotIn('stripe_status', ['cancelled', 'canceled'])
            ->where('status', 'active')
            ->whereIn('stripe_status', ['active','paid'])
            ->get();
            
        if ($expiredSubscriptions->isEmpty()) {
            $this->info('No expired subscriptions found.');
            return 0;
        }
        
        $this->info("Found {$expiredSubscriptions->count()} expired subscriptions to process.");
        
        $processedCount = 0;
        $errorCount = 0;
        
        foreach ($expiredSubscriptions as $subscription) {
            try {
                $user = $subscription->user;
                $userName = $user ? $user->name : 'Unknown User';
                $userEmail = $user ? $user->email : 'Unknown Email';
                
                $this->line("Processing subscription ID: {$subscription->id} for {$userName} ({$userEmail})");
                $this->line("  Expired on: {$subscription->current_period_end}");
                $this->line("  Current status: {$subscription->status}");
                
                if (!$dryRun) {
                    DB::beginTransaction();
                    // Update subscription status
                    $subscription->status = 'ended';
                    $subscription->stripe_status = 'canceled';
                    $subscription->cancelled_at = $currentDateTime;
                    $subscription->ends_at = $currentDateTime;
                    $subscription->save();
                    
                    // Also update related UserDownloads if exists
                    $userDownload = UserDownloads::where('user_subscription_id', $subscription->id)->first();
                    if ($userDownload) {
                        // Set expiration for the download tracker
                        $userDownload->expires_at = $currentDateTime;
                        $userDownload->save();
                        $this->line("  Updated download tracker expiration");
                    }
                    
                    DB::commit();
                    $this->info("  ✓ Subscription marked as cancelled");
                } else {
                    $this->line("  [DRY RUN] Would mark subscription as cancelled");
                }
                
                $processedCount++;
                
            } catch (\Exception $e) {
                DB::rollBack();
                Helpers::sendErrorMailToDeveloper($e);
                $this->error("  ✗ Error processing subscription ID {$subscription->id}: " . $e->getMessage());
                $errorCount++;
            }
        }
        
        // Summary
        $this->newLine();
        if ($dryRun) {
            $this->info("DRY RUN SUMMARY:");
            $this->info("Would process: {$processedCount} subscriptions");
        } else {
            $this->info("PROCESSING SUMMARY:");
            $this->info("Successfully processed: {$processedCount} subscriptions");
        }
        
        if ($errorCount > 0) {
            $this->error("Errors encountered: {$errorCount}");
        }
        
        // Additional check for subscriptions expiring soon (within 3 days)
        $this->checkUpcomingExpirations();
        
        return $errorCount > 0 ? 1 : 0;
    }

        
    /**
     * Check for subscriptions expiring in the next 3 days
     */
    private function checkUpcomingExpirations()
    {
        $this->newLine();
        $this->info('Checking for subscriptions expiring soon...');
        
        $upcomingExpired = UserSubscription::whereBetween('current_period_end', [
                Carbon::now(),
                Carbon::now()->addDays(3)
            ])
            ->whereNotIn('status', ['cancelled', 'canceled', 'ended'])
            ->whereNotIn('stripe_status', ['cancelled', 'canceled', 'ended'])
            ->get();
            
        if ($upcomingExpired->isNotEmpty()) {
            $this->warn("⚠️  {$upcomingExpired->count()} subscriptions will expire in the next 3 days:");
            
            foreach ($upcomingExpired as $subscription) {
                $user = $subscription->user;
                $userName = $user ? $user->name : 'Unknown User';
                $this->line("  - {$userName} (ID: {$subscription->id}) expires on {$subscription->current_period_end}");
            }
        } else {
            $this->info('No subscriptions expiring in the next 3 days.');
        }
    }
}
