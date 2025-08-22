<?php

namespace App\Console\Commands;

use App\Models\FcaNumbers;
use App\Models\User;
use Illuminate\Console\Command;

class CopyExistFcaNumbers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:copy-fca-numbers';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Copy exist fca numbers';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $users = User::select('fca_number', 'company_name')->where('role','!=','admin')->get();
        $this->info('Total users: ' . $users->count());
        
        // copy all exist fca numbers
        foreach ($users as $user) {
            FcaNumbers::updateOrCreate([
                'fca_number' => $user->fca_number,
            ], [
                'fca_name' => $user->company_name,
            ]);
        }

        $this->info('FCA numbers copied successfully.');
    }
}
