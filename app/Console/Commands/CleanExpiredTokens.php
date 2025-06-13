<?php

namespace App\Console\Commands;

use App\Models\UserTokens;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
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
        $tenDaysAgo = Carbon::now()->subDays(10)->toDateTimeString();
        UserTokens::where(function ($query) use ($tenDaysAgo) {
            $query->where('created_at', '<', $tenDaysAgo)
                ->where('is_used', true);
        })->delete();

        Log::info('Expired tokens cleaned successfully');
        return $this->info('Expired tokens cleaned successfully');
    }
}
