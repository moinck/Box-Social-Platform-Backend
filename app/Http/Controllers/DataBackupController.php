<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class DataBackupController extends Controller
{
    public function dailyBackup(){
        // $host = config('database.connections.mysql.host');
        // $username = config('database.connections.mysql.username');
        // $password = config('database.connections.mysql.password');
        // $database = config('database.connections.mysql.database');
        $backup_file = public_path().'/uploads/'. date("d") . '.sql';


        // if (!file_exists(public_path()."/uploads/")) {
        //     mkdir(public_path()."/uploads/", 0777, true);
        // }

        // $command = "mysqldump --opt -h $host -u $username -p$password $database > $backup_file";
        // system($command);        
        
        $path = Storage::disk('digitalocean')->putFileAs('dbbackup', new \Illuminate\Http\File($backup_file),date('Y-m-d').'.sql',"");

        unlink($backup_file);

        exit;
    }
}
