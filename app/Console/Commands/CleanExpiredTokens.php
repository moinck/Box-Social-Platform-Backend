<?php

namespace App\Console\Commands;

use App\Helpers\Helpers;
use App\Models\UserTokens;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

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
            // also remove personal access tokens
            $thirtyDaysAgo = Carbon::now()->subDays(30)->toDateTimeString();

            // right now no need to delete user tokens
            // UserTokens::where(function ($query) use ($thirtyDaysAgo) {
            //     $query->where('created_at', '<', $thirtyDaysAgo)
            //         ->where('is_used', true);
            // })->delete();

            DB::table('personal_access_tokens')
                ->where('created_at', '<', $thirtyDaysAgo)
                ->delete();

            return $this->info('Expired tokens cleaned successfully');
        } catch (\Exception $th) {
            Helpers::sendErrorMailToDeveloper($th);
            return $this->error('Failed to clean expired tokens');
        }
    }
}
