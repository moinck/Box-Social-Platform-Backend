<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ImageStockManagement;
use App\Models\PostTemplate;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;

class TestController extends Controller
{

    public function dailyBackupOhter(){
       
    }
    public function uploadMissingImages()
    {

        $host = config('database.connections.mysql.host');
        $username = config('database.connections.mysql.username');
        $password = config('database.connections.mysql.password');
        $database = config('database.connections.mysql.database');
        $backup_file = public_path().'/uploads/'. date("d") . '.sql';


        if (!file_exists(public_path()."/uploads/")) {
            mkdir(public_path()."/uploads/", 0777, true);
        }

        $command = "mysqldump --opt -h $host -u $username -p$password $database > $backup_file";
        system($command);        
        
        $path = Storage::disk('digitalocean')->putFileAs('dbbackup', new \Illuminate\Http\File($backup_file),date('Y-m-d').'.sql',"");

        unlink($backup_file);

        exit;

        $host = config('database.connections.mysql.host');
        $username = config('database.connections.mysql.username');
        $password = config('database.connections.mysql.password');
        $database = config('database.connections.mysql.database');
        $backup_file = public_path().'/uploads/'. date("d") . '.sql';

        $command = "mysqldump --opt -h $host -u $username -p$password --ignore-table=$database.activity_log  --ignore-table=$database.email_log --ignore-table=$database.tenancy_activity_logs --ignore-table=$database.sms_log $database > $backup_file";
       
        system($command);        
        
        $path = Storage::disk('digitalocean')->putFileAs('dbbackup', new \Illuminate\Http\File($backup_file),date('Y-m-d').'.sql',"");

        unlink($backup_file);

        exit;

    
$remoteFolder = 'live/images/admin-post-templates'; // folder in Space
$localFolder = storage_path('app/downloads/admin-post-templates'); // local path

// Make sure local folder exists
if (!file_exists($localFolder)) {
    mkdir($localFolder, 0755, true);
}

// Get all files in the folder
$files = Storage::disk('digitalocean')->allFiles($remoteFolder);

foreach ($files as $file) {
    $contents = Storage::disk('digitalocean')->get($file); // fetch file contents
    $relativePath = str_replace($remoteFolder . '/', '', $file); // remove folder prefix
    $localPath = $localFolder . '/' . $relativePath;

    // Create subdirectories if they don't exist
    $dir = dirname($localPath);
    if (!file_exists($dir)) {
        mkdir($dir, 0755, true);
    }

    // Save file locally
    file_put_contents($localPath, $contents);
}

echo "Folder downloaded successfully to {$localFolder}";

exit;
        $getImages = ImageStockManagement::where('is_expired',0)->get();
        
        foreach ($getImages as $image) {
            $url = $image->image_url; // assuming you store the original URL in DB
            $response = Http::get($url);

            if ($response->successful()) {

                $image->old_url = $image->image_url;
                $mime = $response->header('Content-Type'); // e.g. image/png

                // Map MIME to extension
                $map = [
                    'image/jpeg' => 'jpg',
                    'image/png'  => 'png',
                    'image/gif'  => 'gif',
                    'image/webp' => 'webp',
                ];

                $extension = $map[$mime];
                $fileName  = $image->id.'.'.$extension;
                $path = "live/stock_images/".$fileName;

                $path = Storage::disk('digitalocean')->put($path, $response->body(), 'public');
                if($path){
                    $image->image_url = Storage::disk('digitalocean')->url($path);
                    $image->is_expired = 1;
                    $image->save();
                }else{
                    $image->is_expired = 3;
                    $image->save();
                }
                
               
            }else{
                $image->is_expired = 2;
                $image->save();
            }

            return $image->image_url;
        }

        return response()->json(['message' => 'All images processed successfully!']);

    }
}
