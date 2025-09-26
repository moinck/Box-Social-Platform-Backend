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

    public function uploadMissingImages()
    {  

    
$remoteFolder = 'dbbackup'; // folder in Space
// Get all files in the folder
$files = Storage::disk('digitalocean')->allFiles($remoteFolder);
dd($files);
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
        
     
        return response()->json(['message' => 'All images processed successfully!']);

    }
}
