<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class DataBackupController extends Controller
{
    public function dailyBackup(){
        exit;
        // $date = date('Y-m-d');

        // $filePath = 'dbbackup/' . $date . '.sql';

    //   $disk = Storage::disk('digitalocean');
    //   if ($disk->exists($filePath)) {
    //     $fileSize = $disk->size($filePath); // File size in bytes
    //     $fileSizeInKB = $fileSize / 1024;
    //     $fileSizeInMB = $fileSizeInKB / 1024;

    //     $filePathSql = 'dbbackup/' . $date . '.sql';

    //     return "File size: {$fileSize} bytes ({$fileSizeInKB} KB / {$fileSizeInMB} MB)";
    //   } 

    //     if (!file_exists(public_path()."/storage/cronlog")) {
    //         mkdir(public_path()."/storage/cronlog", 0777, true);
    //     }

    //     $logDate = date("Y-m-d");
    //     $logFileHandle = fopen(public_path()."/storage/cronlog/$logDate.txt",'w');

    //     $host = config('database.connections.mysql.host');
    //     $username = config('database.connections.mysql.username');
    //     $password = config('database.connections.mysql.password');
    //     $database = config('database.connections.mysql.database');

    //     $backup_file = public_path().'/uploads/'. date("d") . '.sql';

    //     // Tables to ignore
    //     $ignore_tables = [
    //         "$database.post_templates",
    //         "$database.post_templates2"
    //     ];

    //     // Build the --ignore-table options
    //     $ignore_options = '';
    //     foreach ($ignore_tables as $table) {
    //         $ignore_options .= ' --ignore-table=' . escapeshellarg($table);
    //     }

    //     // Build the final command
    //     $command = sprintf(
    //         'mysqldump --opt -h %s -u %s -p%s%s %s > %s',
    //         escapeshellarg($host),
    //         escapeshellarg($username),
    //         escapeshellarg($password),
    //         $ignore_options,
    //         escapeshellarg($database),
    //         escapeshellarg($backup_file)
    //     );


    //     // Execute it
    //     system($command);

        $path = Storage::disk('digitalocean')->putFileAs('dbbackup', new \Illuminate\Http\File($backup_file),date('Y-m-d').'.sql',"");

        dd($path);

        $logString = "1. database backup :".time();
        fwrite($logFileHandle, $logString);


        unlink($backup_file);

        exit;
        
       
    }
}
