<?php

namespace App\Console\Commands;

use App\Helpers\Helpers;
use App\Models\UserDownloads;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class ResetMonthlyDownloads extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'downloads:reset-monthly';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reset monthly download counters for premium users';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting monthly download reset...');
        
        $currentDate = Carbon::now();
        
        // Only run on the 1st day of the month
        if ($currentDate->day !== 1) {
            $this->info('Not the 1st day of the month. Skipping reset.');
            return;
        }
        
        // Get all premium users who need reset
        $usersToReset = UserDownloads::where('plan_type', '!=', 'free-trial')
            ->where(function($query) use ($currentDate) {
                $query->where('current_month', '!=', $currentDate->month)
                      ->orWhere('current_year', '!=', $currentDate->year);
            })
            ->get();
            
        $resetCount = 0;
        
        foreach ($usersToReset as $userDownload) {
            try {
                DB::beginTransaction();
                // Calculate carry over (unused downloads from previous month)
                // $effectiveLimit = $userDownload->monthly_limit + $userDownload->carried_over_downloads;
                // $unusedDownloads = max(0, $effectiveLimit - $userDownload->monthly_downloads_used);
                
                // Reset for new month
                $userDownload->update([
                    'monthly_downloads_used' => 0,
                    'current_month' => $currentDate->month,
                    'current_year' => $currentDate->year,
                    'last_reset_date' => $currentDate->toDateString(),
                    'carried_over_downloads' => 0,
                ]);

                $resetCount++;
                DB::commit();
                
                $this->line("Reset user {$userDownload->user_id}: downloads carried over");
            } catch (\Exception $e) {
                DB::rollBack();
                Helpers::sendErrorMailToDeveloper($e);
                $this->error("Failed to reset user {$userDownload->user_id}: " . $e->getMessage());
            }
        }
        
        $this->info("Successfully reset {$resetCount} user download counters.");
        
        return 0;
    }
}
