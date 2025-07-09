<?php

namespace App\Console\Commands;

use App\Models\UserTokens;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CleanExpiredTokens extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:clean-expired-tokens';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean expired tokens';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        try {
            $tenDaysAgo = Carbon::now()->subDays(10)->toDateTimeString();
            UserTokens::where(function ($query) use ($tenDaysAgo) {
                $query->where('created_at', '<', $tenDaysAgo)
                    ->where('is_used', true);
            })->delete();

            // also remove personal access tokens
            $twentyDaysAgo = Carbon::now()->subDays(20)->toDateTimeString();
            DB::table('personal_access_tokens')
                ->where('created_at', '<', $twentyDaysAgo)
                ->delete();

            Log::info('Expired tokens cleaned successfully', [
                'today' => Carbon::now()->toDateTimeString(),
            ]);
            return $this->info('Expired tokens cleaned successfully');
        } catch (\Exception $th) {
            Log::error('Failed to clean expired tokens', [
                'today' => Carbon::now()->toDateTimeString(),
                'error' => $th->getMessage(),
                'trace' => $th->getTraceAsString(),
            ]);
            return $this->error('Failed to clean expired tokens');
        }
    }
}
