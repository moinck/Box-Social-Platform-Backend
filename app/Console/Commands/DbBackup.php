<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Storage;

class DbBackup extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:db-backup';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $name = 'db-backup-'.uniqid()."-".date('Y-m-d_H-i-s').'.sql.gz';
        $this->info("started backup process with name : {$name}");

        $dbUsername = env('DB_USERNAME');
        $dbPassword = env('DB_PASSWORD');
        $dbHost = env('DB_HOST');
        $dbDatabase = env('DB_DATABASE');

        $backupDir = Storage::disk('local')->path('db-backup');
        if (!file_exists($backupDir)) {
            mkdir($backupDir, 0755, true);
        }

        $path = $backupDir.'/'.$name;

        $command = "mysqldump --user={$dbUsername} --password={$dbPassword} --host={$dbHost} --single-transaction --quick {$dbDatabase} | gzip > {$path}";

        $process = Process::run($command);

        if ($process->successful()) {
            $this->info("Database backup completed successfully. Path: {$path}");
        } else {    
            $this->error("Database backup failed. Error: {$process->errorOutput()}");
        }
    }
}
