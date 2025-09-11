<?php

namespace App\Helpers;

use App\Events\NewNotificationEvent;
use App\Mail\DynamicContentMail;
use App\Mail\RegisterVerificationMail;
use App\Models\BrandKit;
use App\Models\FcaNumbers;
use App\Models\ImageStockManagement;
use App\Models\Notification;
use App\Models\User;
use App\Models\UserDownloads;
use App\Models\UserSubscription;
use App\Models\UserTemplates;
use App\Models\UserTokens;
use App\Notifications\CustomVerifyEmail;
use Barryvdh\DomPDF\Facade\Pdf;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Pusher\Pusher;
use Stripe\Exception\ApiErrorException;
use Stripe\Exception\InvalidRequestException;
use Stripe\StripeClient;

class Helpers
{
    public static function appClasses()
    {

        $data = config('custom.custom');


        // default data array
        $DefaultData = [
            'myLayout' => 'vertical',
            'myTheme' => 'theme-default',
            'myStyle' => 'light',
            'myRTLSupport' => true,
            'myRTLMode' => true,
            'hasCustomizer' => true,
            'showDropdownOnHover' => true,
            'displayCustomizer' => true,
            'contentLayout' => 'compact',
            'headerType' => 'fixed',
            'navbarType' => 'fixed',
            'menuFixed' => true,
            'menuCollapsed' => false,
            'footerFixed' => false,
            'menuFlipped' => false,
            // 'menuOffcanvas' => false,
            'customizerControls' => [
                'rtl',
                'style',
                'headerType',
                'contentLayout',
                'layoutCollapsed',
                'showDropdownOnHover',
                'layoutNavbarOptions',
                'themes',
            ],
            //   'defaultLanguage'=>'en',
        ];

        // if any key missing of array from custom.php file it will be merge and set a default value from dataDefault array and store in data variable
        $data = array_merge($DefaultData, $data);

        // All options available in the template
        $allOptions = [
            'myLayout' => ['vertical', 'horizontal', 'blank', 'front'],
            'menuCollapsed' => [true, false],
            'hasCustomizer' => [true, false],
            'showDropdownOnHover' => [true, false],
            'displayCustomizer' => [true, false],
            'contentLayout' => ['compact', 'wide'],
            'headerType' => ['fixed', 'static'],
            'navbarType' => ['fixed', 'static', 'hidden'],
            'myStyle' => ['light', 'dark', 'system'],
            'myTheme' => ['theme-default', 'theme-bordered', 'theme-semi-dark'],
            'myRTLSupport' => [true, false],
            'myRTLMode' => [true, false],
            'menuFixed' => [true, false],
            'footerFixed' => [true, false],
            'menuFlipped' => [true, false],
            // 'menuOffcanvas' => [true, false],
            'customizerControls' => [],
            // 'defaultLanguage'=>array('en'=>'en','fr'=>'fr','de'=>'de','ar'=>'ar'),
        ];

        //if myLayout value empty or not match with default options in custom.php config file then set a default value
        foreach ($allOptions as $key => $value) {
            if (array_key_exists($key, $DefaultData)) {
                if (gettype($DefaultData[$key]) === gettype($data[$key])) {
                    // data key should be string
                    if (is_string($data[$key])) {
                        // data key should not be empty
                        if (isset($data[$key]) && $data[$key] !== null) {
                            // data key should not be exist inside allOptions array's sub array
                            if (!array_key_exists($data[$key], $value)) {
                                // ensure that passed value should be match with any of allOptions array value
                                $result = array_search($data[$key], $value, 'strict');
                                if (empty($result) && $result !== 0) {
                                    $data[$key] = $DefaultData[$key];
                                }
                            }
                        } else {
                            // if data key not set or
                            $data[$key] = $DefaultData[$key];
                        }
                    }
                } else {
                    $data[$key] = $DefaultData[$key];
                }
            }
        }
        $styleVal = $data['myStyle'] == "dark" ? "dark" : "light";
        $styleUpdatedVal = $data['myStyle'] == "dark" ? "dark" : $data['myStyle'];
        // Determine if the layout is admin or front based on cookies
        $layoutName = $data['myLayout'];
        $isAdmin = Str::contains($layoutName, 'front') ? false : true;

        $modeCookieName = $isAdmin ? 'admin-mode' : 'front-mode';
        $colorPrefCookieName = $isAdmin ? 'admin-colorPref' : 'front-colorPref';

        // Determine style based on cookies, only if not 'blank-layout'
        if ($layoutName !== 'blank') {
            if (isset($_COOKIE[$modeCookieName])) {
                $styleVal = $_COOKIE[$modeCookieName];
                if ($styleVal === 'system') {
                    $styleVal = isset($_COOKIE[$colorPrefCookieName]) ? $_COOKIE[$colorPrefCookieName] : 'light';
                }
                $styleUpdatedVal = $_COOKIE[$modeCookieName];
            }
        }

        isset($_COOKIE['theme']) ? $themeVal = $_COOKIE['theme'] : $themeVal = $data['myTheme'];

        $directionVal = isset($_COOKIE['direction']) ? ($_COOKIE['direction'] === "true" ? 'rtl' : 'ltr') : $data['myRTLMode'];

        //layout classes
        $layoutClasses = [
            'layout' => $data['myLayout'],
            'theme' => $themeVal,
            'themeOpt' => $data['myTheme'],
            'style' => $styleVal,
            'styleOpt' => $data['myStyle'],
            'styleOptVal' => $styleUpdatedVal,
            'rtlSupport' => $data['myRTLSupport'],
            'rtlMode' => $data['myRTLMode'],
            'textDirection' => $directionVal, //$data['myRTLMode'],
            'menuCollapsed' => $data['menuCollapsed'],
            'hasCustomizer' => $data['hasCustomizer'],
            'showDropdownOnHover' => $data['showDropdownOnHover'],
            'displayCustomizer' => $data['displayCustomizer'],
            'contentLayout' => $data['contentLayout'],
            'headerType' => $data['headerType'],
            'navbarType' => $data['navbarType'],
            'menuFixed' => $data['menuFixed'],
            'footerFixed' => $data['footerFixed'],
            'menuFlipped' => $data['menuFlipped'],
            'customizerControls' => $data['customizerControls'],
        ];

        // sidebar Collapsed
        if ($layoutClasses['menuCollapsed'] == true) {
            $layoutClasses['menuCollapsed'] = 'layout-menu-collapsed';
        }

        // Header Type
        if ($layoutClasses['headerType'] == 'fixed') {
            $layoutClasses['headerType'] = 'layout-menu-fixed';
        }
        // Navbar Type
        if ($layoutClasses['navbarType'] == 'fixed') {
            $layoutClasses['navbarType'] = 'layout-navbar-fixed';
        } elseif ($layoutClasses['navbarType'] == 'static') {
            $layoutClasses['navbarType'] = '';
        } else {
            $layoutClasses['navbarType'] = 'layout-navbar-hidden';
        }

        // Menu Fixed
        if ($layoutClasses['menuFixed'] == true) {
            $layoutClasses['menuFixed'] = 'layout-menu-fixed';
        }


        // Footer Fixed
        if ($layoutClasses['footerFixed'] == true) {
            $layoutClasses['footerFixed'] = 'layout-footer-fixed';
        }

        // Menu Flipped
        if ($layoutClasses['menuFlipped'] == true) {
            $layoutClasses['menuFlipped'] = 'layout-menu-flipped';
        }

        // Menu Offcanvas
        // if ($layoutClasses['menuOffcanvas'] == true) {
        //   $layoutClasses['menuOffcanvas'] = 'layout-menu-offcanvas';
        // }

        // RTL Supported template
        if ($layoutClasses['rtlSupport'] == true) {
            $layoutClasses['rtlSupport'] = '/rtl';
        }

        // RTL Layout/Mode
        if ($layoutClasses['rtlMode'] == true) {
            $layoutClasses['rtlMode'] = 'rtl';
            $layoutClasses['textDirection'] = isset($_COOKIE['direction']) ? ($_COOKIE['direction'] === "true" ? 'rtl' : 'ltr') : 'rtl';
        } else {
            $layoutClasses['rtlMode'] = 'ltr';
            $layoutClasses['textDirection'] = isset($_COOKIE['direction']) && $_COOKIE['direction'] === "true" ? 'rtl' : 'ltr';
        }

        // Show DropdownOnHover for Horizontal Menu
        if ($layoutClasses['showDropdownOnHover'] == true) {
            $layoutClasses['showDropdownOnHover'] = true;
        } else {
            $layoutClasses['showDropdownOnHover'] = false;
        }

        // To hide/show display customizer UI, not js
        if ($layoutClasses['displayCustomizer'] == true) {
            $layoutClasses['displayCustomizer'] = true;
        } else {
            $layoutClasses['displayCustomizer'] = false;
        }

        return $layoutClasses;
    }

    public static function updatePageConfig($pageConfigs)
    {
        $demo = 'custom';
        if (isset($pageConfigs)) {
            if (count($pageConfigs) > 0) {
                foreach ($pageConfigs as $config => $val) {
                    Config::set('custom.' . $demo . '.' . $config, $val);
                }
            }
        }
    }

    /**
     * function to save images
     */
    public static function uploadImage($prefix, $image, $path, $disk = 'digitalocean')
    {
        try {
            // For local storage, ensure directory exists
            if ($disk === 'public' && !Storage::disk('public')->exists($path)) {
                Storage::disk('public')->makeDirectory($path);
            }

            // add enviroment name
            $envName = config('app.env') ?? env('APP_ENV','local');
            
            // Generate a unique name for the image
            $image_name = $prefix . "_" . uniqid() . '.' . $image->getClientOriginalExtension();

            $newPath = $envName . "/" . $path;
            
            // Store the image
            $storedPath = $image->storeAs($newPath, $image_name, $disk);
            
            if (!$storedPath) {
                throw new Exception("Failed to upload image to {$disk} disk");
            }
            
            // Return appropriate URL based on disk
            if ($disk === 'digitalocean') {
                $imageUrl = Storage::disk('digitalocean')->url($storedPath);
            } else {
                $imageUrl = 'storage/' . $newPath . '/' . $image_name;
            }
            
            // // Log::info("Image uploaded successfully to {$disk}: " . $imageUrl);
            return $imageUrl;
            
        } catch (Exception $e) {
            self::sendErrorMailToDeveloper($e);
            // // Log::error("Error uploading image to {$disk}: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Test DigitalOcean connection
     * @return array
     */
    public static function testDigitalOceanConnection()
    {
        try {
            $disk = Storage::disk('digitalocean');
            
            // Test basic connection by trying to list files
            $directory = env('APP_ENV') . '/images/';
            $files = $disk->allFiles($directory);
            
            return [
                'success' => true,
                'message' => 'DigitalOcean connection successful',
                'file_count' => count($files),
                'files' => $files
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'DigitalOcean connection failed: ' . $e->getMessage(),
                'error' => $e->getTraceAsString()
            ];
        }
    }
        
    /**
     * Function to delete image
     * @param mixed $path
     * @return void
     */
    public static function deleteImageOld($path)
    {
        try {
            // Remove 'storage/' prefix if it exists
            $storagePath = str_replace('storage/', '', $path);
            
            if (Storage::disk('public')->exists($storagePath)) {
                // Storage::disk('public')->delete($storagePath);
                // Log::info("File deleted successfully: " . $path);
            } else {
                // Log::info("File does not exist at: " . $path);
            }
        } catch (Exception $e) {
            // Log::error("Error deleting file: " . $e->getMessage());
        }
    }

    /**
     * Function to delete image from both local storage and DigitalOcean
     * @param string $path
     * @return void
     */
    public static function deleteImage($path)
    {
        try {
            // Check if it's a DigitalOcean URL
            if (str_contains($path, env('DO_SPACES_URL')) || str_contains($path, env('DO_SPACES_ENDPOINT'))) {
                // Extract the file path from the full URL
                $doSpacesUrl = env('DO_SPACES_URL') ?: env('DO_SPACES_ENDPOINT');
                $relativePath = str_replace($doSpacesUrl . '/', '', $path);
                
                if (Storage::disk('digitalocean')->exists($relativePath)) {
                    // Storage::disk('digitalocean')->delete($relativePath);
                    // Log::info("File deleted successfully from DigitalOcean: " . $path);
                } else {
                    // Log::info("File does not exist in DigitalOcean: " . $path);
                }
            } else {
                // Handle local storage files (backward compatibility)
                $storagePath = str_replace('storage/', '', $path);
                
                if (Storage::disk('public')->exists($storagePath)) {
                    // Storage::disk('public')->delete($storagePath);
                    // Log::info("File deleted successfully from local storage: " . $path);
                } else {
                    // Log::info("File does not exist in local storage: " . $path);
                }
            }
        } catch (Exception $e) {
            self::sendErrorMailToDeveloper($e);
            // Log::error("Error deleting file: " . $e->getMessage());
        }
    }
    

    static $secretKey = 'rT9vL2pN6xBzCqA3WmEyKdSfUjHgXzV1';
    static $secretIv = 'Yt6MnBpQaWxCrV8dLfZuKiJhGvTeSdR4';

    /**
     * Returns encrypted original string
     *
     * @param  $string - Enctrypted string
     *
     * @return string
     */
    public static function encrypt($string) {
        $output = false;
        $encrypt_method = "AES-256-CBC";
        //pls set your unique hashing key
        // hash
        $key = hash('sha256', self::$secretKey);

        // iv - encrypt method AES-256-CBC expects 16 bytes - else you will get a warning
        $iv = substr(hash('sha256', self::$secretIv), 0, 16);

        $output = openssl_encrypt($string, $encrypt_method, $key, 0, $iv);
        $output = base64_encode($output);

        return $output;
    }
    /**
     * Returns decrypted original string
     *
     * @param  $string - Enctrypted string
     *
     * @return string
     */
    public static function decrypt($string) {

        $output = false;
        $encrypt_method = "AES-256-CBC";
        //pls set your unique hashing key
        $secret_key = 'iih';
        $secret_iv = 'iih';

        // hash
        $key = hash('sha256', self::$secretKey);

        // iv - encrypt method AES-256-CBC expects 16 bytes - else you will get a warning
        $iv = substr(hash('sha256', self::$secretIv), 0, 16);

        $output = openssl_decrypt(base64_decode($string), $encrypt_method, $key, 0, $iv);

        return $output;
    }

    /**
     * Helper function to get file extension from MIME type
     */
    public static function getExtensionFromMimeType($mimeType)
    {
        $mimeToExt = [
            'image/jpeg' => 'jpg',
            'image/jpg' => 'jpg',
            'image/png' => 'png',
            'image/gif' => 'gif',
            'image/webp' => 'webp',
            'image/bmp' => 'bmp',
            'image/svg+xml' => 'svg',
        ];

        return $mimeToExt[$mimeType] ?? null;
    }

    public static function uploadImageFromUrl($prefix, $imageUrl, $path)
    {
        try {
            // Validate URL format
            if (!filter_var($imageUrl, FILTER_VALIDATE_URL)) {
                throw new Exception('Invalid URL format provided');
            }

            // Validate URL scheme (only allow HTTP/HTTPS)
            $scheme = parse_url($imageUrl, PHP_URL_SCHEME);
            if (!in_array($scheme, ['http', 'https'])) {
                throw new Exception('Only HTTP/HTTPS URLs are allowed');
            }

            // Fetch image using HTTP client with timeout and size limits
            // if image has HTTPs then only fetch, else get from storage
            // if (str_contains($imageUrl, 'https://')) {
                $response = Http::timeout(30)
                    ->withHeaders([
                        'User-Agent' => 'ImageUploader/1.0',
                    ])
                    ->get($imageUrl);
            // } else {
            //     $response = file_get_contents($imageUrl);
            // }

            if (!$response->successful()) {
                throw new Exception('Failed to fetch image from URL. HTTP Status: ' . $response->status());
            }

            $imageContent = $response->body();

            // Check file size limit (5MB)
            $maxSize = 5 * 1024 * 1024;
            if (strlen($imageContent) > $maxSize) {
                throw new Exception('Image file too large. Maximum size allowed: 5MB');
            }

            // Validate image format and get info
            $imageInfo = @getimagesizefromstring($imageContent);
            if ($imageInfo === false) {
                throw new Exception('Invalid image format or corrupted image');
            }

            // Validate image dimensions
            $maxWidth = 4000;
            $maxHeight = 4000;
            if ($imageInfo[0] > $maxWidth || $imageInfo[1] > $maxHeight) {
                throw new Exception("Image dimensions too large. Maximum: {$maxWidth}x{$maxHeight}px");
            }

            // Validate MIME type
            $allowedMimeTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            if (!in_array($imageInfo['mime'], $allowedMimeTypes)) {
                throw new Exception('Unsupported image format. Allowed: JPEG, PNG, GIF, WebP');
            }

            // Get file extension from MIME type
            $extension = self::getExtensionFromMimeType($imageInfo['mime']);
            if (!$extension) {
                throw new Exception('Could not determine file extension');
            }

            // Generate unique filename
            $image_name = $prefix . "_" . $extension;
            $tempPath = storage_path('app/temp/' . $image_name);

            // Create temp directory if it doesn't exist
            $tempDir = dirname($tempPath);
            if (!file_exists($tempDir)) {
                if (!mkdir($tempDir, 0755, true)) {
                    throw new Exception('Failed to create temporary directory');
                }
            }

            // Save image to temporary file
            if (file_put_contents($tempPath, $imageContent) === false) {
                throw new Exception('Failed to save temporary file');
            }

            // Additional validation: re-check the saved file
            if (Storage::disk('public')->exists($tempPath)) {
                throw new Exception('Image validation failed after download');
            }

            // Create UploadedFile instance
            $uploadedFile = new \Illuminate\Http\UploadedFile(
                $tempPath,
                $image_name,
                $imageInfo['mime'],
                null,
                true
            );

            // Upload using your existing upload method
            $uploadedImageUrl = self::uploadImage($prefix, $uploadedFile, $path);

            // Clean up temporary file
            if (file_exists($tempPath)) {
                unlink($tempPath);
            }

            if (!$uploadedImageUrl) {
                throw new Exception('Failed to upload image to final destination');
            }

            return $uploadedImageUrl;

        } catch (Exception $e) {
            // Clean up temporary file if it exists
            if (isset($tempPath) && file_exists($tempPath)) {
                unlink($tempPath);
            }

            // Log detailed error information
            Log::error('Image upload from URL failed', [
                'url' => $imageUrl,
                'prefix' => $prefix,
                'path' => $path,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return false;
        }
    }

    public static function dateFormate($date)
    {
        return \Carbon\Carbon::parse($date)->format('d-m-Y | h:i A');
    }

    /**
     * handle Base64 Image upload
     * @param mixed $base64String
     * @param mixed $prefix
     * @param mixed $path
     * @return string
     */
    public static function handleBase64Image($base64String,$prefix,$path)
    {
        // Extract the base64 data and mime type
        $data = explode(',', $base64String);
        $mimeType = explode(';', explode(':', $data[0])[1])[0];
        $base64Data = $data[1];

        // Decode base64 data
        $imageData = base64_decode($base64Data);

        // Generate a unique filename
        $extension = explode('/', $mimeType)[1];
        // if extension is svg+xml then change it to svg
        if ($extension == 'svg+xml') {
            $extension = 'svg';
        }
        $filename = 'logo_' . uniqid() . '.' . $extension;
        $tempPath = storage_path('app/temp/' . $filename);

        // Create temp directory if it doesn't exist
        if (!file_exists(dirname($tempPath))) {
            mkdir(dirname($tempPath), 0755, true);
        }

        // Save the decoded image to a temporary file
        file_put_contents($tempPath, $imageData);

        // Create an UploadedFile instance
        $uploadedFile = new \Illuminate\Http\UploadedFile(
            $tempPath,
            $filename,
            $mimeType,
            null,
            true
        );

        // Pass the uploaded file to your helper function
        $logoUrl = self::uploadImage($prefix, $uploadedFile, $path);

        // Clean up the temporary file
        if (file_exists($tempPath)) {
            unlink($tempPath);
        }

        return $logoUrl;
    }

    /**
     * Generate verification token
     * @param mixed $user
     * @return string
     */
    public static function generateVarificationToken($user,Request $request,$type)
    {
        $tokenData = bin2hex(random_bytes(32));

        $userToken = new UserTokens();
        $userToken->user_id = $user->id;
        $userToken->token = $tokenData;
        $userToken->type = $type;
        $userToken->is_used = false;
        $userToken->ip_address = $request->ip();
        $userToken->user_agent = $request->userAgent();
        $userToken->save();

        // Encrypt the entire token data
        $encryptedToken = $tokenData;

        return $encryptedToken;
    }

    /**
     * Send verification mail to user
     * @param mixed $user
     * @return string
     */
    public static function sendVerificationMail($user,$token)
    {
        // $token = self::generateVarificationToken($user,$request);

        // $user->notify(new CustomVerifyEmail($token));
        Mail::to($user->email)->send(new RegisterVerificationMail($token));

        return true;
    }

    /**
     * Send notification to admin
     * @param mixed $data
     * @param mixed $type
     * @return bool
     */
    public static function sendNotification($data, $type)
    {
        $title = "";
        $body = "";
        $fullName = ($data->first_name ?? '')." ".($data->last_name ?? '');
        $dataId = $data->id ?? null;
        switch ($type) {
            case 'new-registration':
                $title = "New Registration";
                $body = "New user ".$fullName." registered";
                break;
            case 'new-contact-us':
                $title = "New Feedback";
                $body = "New feedback is given by ".$data->name;
                $dataId = null;
                break;
            case 'new-subscription':
                $title = "New Subscription";
                $body = "User " .$fullName." taken subscription";
                break;
            case 'subscription-expiring-soon':
                $title = "Subscription Expiration";
                $body = "The subscription for user " .$fullName." will expire on ".Carbon::parse($data->expiring_at)->format('d-m-Y');
                break;
            case 'subscription-failed':
                $title = "Payment Failed";
                $body = "The subscription payment of user ".$fullName." is failed.";
                break;
            default:
                $title = "New Notification";
                $body = "New notification is submitted";
                $dataId = null;
                break;
        }

        if($title && $body){
            $newnotification = Notification::create([
                'user_id' => $dataId,
                'tital' => $title,
                'body' => $body,
                'type' => $type,
                'is_read' => false,
            ]);

            // New Pusher instance
            $pusher = new Pusher(
                config('broadcasting.connections.pusher.key'),
                config('broadcasting.connections.pusher.secret'),
                config('broadcasting.connections.pusher.app_id'),
                config('broadcasting.connections.pusher.options')
            );

            $pusher->trigger('admin-notifications', 'new-notification', [
                'id' => Helpers::encrypt($newnotification->id),
                'type' => $newnotification->type,
                'title' => $newnotification->tital,
                'body' => $newnotification->body,
                'created_at' => $newnotification->created_at->format('Y-m-d H:i:s'),
            ]);
        }

        return true;
    }
    
    /**
     * Function to replace text in fabric template
     * @param mixed $templateJson
     * @param mixed $brandkitData
     */
    public static function replaceFabricTemplateData($templateJson, $brandkitData)
    {
        try {
            // Decode JSON
            $data = json_decode($templateJson, true);
            
            if (!$data || !isset($data['objects'])) {
                return $templateJson; // Return original if invalid
            }

            // Process each object
            foreach ($data['objects'] as &$object) {

                // // for text replacement
                // if (isset($object['boxType']) && isset($object['text'])) {
                //     $boxType = $object['boxType'];
                //     // Replace text based on boxType
                //     switch ($boxType) {
                //         case 'email':
                //             if (isset($brandkitData['email'])) {
                //                 $object['text'] = $brandkitData['email'];
                //             }
                //             break;
                //         case 'phone':
                //             if (isset($brandkitData['phone'])) {
                //                 $object['text'] = $brandkitData['phone'];
                //             }
                //             break;
                //         case 'name':
                //             if (isset($brandkitData['name'])) {
                //                 $object['text'] = $brandkitData['name'];
                //             }
                //             break;
                //         case 'company':
                //             if (isset($brandkitData['company'])) {
                //                 $object['text'] = $brandkitData['company'];
                //             }
                //             break;
                //         case 'address':
                //             if (isset($brandkitData['address'])) {
                //                 $object['text'] = $brandkitData['address'];
                //             }
                //             break;
                //         case 'website':
                //             if (isset($brandkitData['website'])) {
                //                 $object['text'] = $brandkitData['website'];
                //             }
                //             break;
                //     }
                // }

                // // for image replacement
                // if (isset($object['type']) && $object['type'] == 'Image' && isset($object['boxType'])) {
                //     $boxType = $object['boxType'];
                //     switch ($boxType) {
                //         case 'brandkit_logo':
                //             if (isset($brandkitData['brandkit_logo'])) {
                //                 $height = $object['height'];
                //                 $width = $object['width'];
                //                 $base64Logo = self::imageToBase64($brandkitData['brandkit_logo'],$height,$width);
                //                 $object['src'] = $base64Logo;
                //             }
                //             break;
                //     }
                // }

                // for null replacement
                if (isset($object['type']) && $object['type'] == 'Textbox' && $object['text'] == null) {
                    $object['text'] = "";
                }
            }

            // Return updated JSON
            return json_encode($data);
            
        } catch (Exception $e) {
            if(env('APP_ENV') == 'local'){
                dd($e);
            } else {
                self::sendErrorMailToDeveloper($e);
            }
            // Return original data if error occurs
            return $templateJson;
        }
    }

    /**
     * Convert image to base64
     * @param mixed $imagePath
     * @return string
     */
    public static function imageToBase64($imagePath,$height = null,$width = null)
    {
        $path = $imagePath;
        $mime = pathinfo($path, PATHINFO_EXTENSION);
        if ($mime == 'svg') {
            $mime = 'svg+xml';
        }
        // if($height && $width){
        //     $image = imagecreatefromjpeg($path);
        //     $width = imagesx($image);
        //     $height = imagesy($image);
        // }
        $base64Image = 'data:image/' . $mime . ';base64,' . base64_encode(file_get_contents($path));
        return $base64Image;
    }

    /**
     * Function to send error mail to developer
     * @param mixed $errorData
     * @return void
     */
    public static function sendErrorMailToDeveloper($errorData,$functionName = 'New Error Report')
    {
        $newErrorData = [
            'environment' => config('app.env'),
            'url' => request()->fullUrl(),
            'method' => request()->method(),
            'ipAddress' => request()->ip(),
            'userAgent' => request()->userAgent(),
            'file' => $errorData->getFile(),
            'line' => $errorData->getLine(),
            'trace' => $errorData->getTraceAsString(),
            'message' => $errorData->getMessage(),
            'functionName' => $functionName,
            'exception' => get_class($errorData),
            'version' => config('app.version') ?? "1.0",
        ];

        $view = view('content.email.error-report-email', compact('newErrorData'))->render();
        if (env('APP_ENV') != 'local') {
            // send mail to developer
            Mail::send([], [], function ($message) use ($view) {
                $message->html($view);
                $message->to(['pratikdev.iihglobal@gmail.com','jayp.iihglobal@gmail.com','martik.iihglobal@gmail.com']);
                $message->subject('New Error Report');
            });
        }
    }

    /**
     * common function to send mail
     * @param mixed $mailableClass
     * @param mixed $to
     * @return bool
     */
    public static function sendMail($mailableClass,$to)
    {
        Mail::to($to)->send($mailableClass);

        return true;
    }

    /**
     * Convert image URL to base64 format
     * 
     * @param string $imagePath
     * @param int $timeout Timeout in seconds (default: 30)
     * @return string|null Returns base64 string or null on failure
     */
    public static function imageUrlToBase64($imagePath, $timeout = 30)
    {
        try {
            if (empty($imagePath)) {
                Log::warning('Empty image path provided');
                return null;
            }

            // Check if it's a local storage path (doesn't start with http)
            if (!str_starts_with($imagePath, 'http')) {
                return self::handleLocalImage($imagePath);
            }

            // Handle external URL
            return self::handleExternalImage($imagePath, $timeout);

        } catch (Exception $e) {
            Log::error('Error converting image to base64', [
                'path' => $imagePath,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Handle local storage images
     */
    public static function handleLocalImage($imagePath)
    {
        // Remove leading slash if present
        $imagePath = ltrim($imagePath, '/');
        
        // Get full path from public directory
        $fullPath = public_path($imagePath);
        
        // Check if file exists
        if (!file_exists($fullPath)) {
            Log::warning('Local image file not found', ['path' => $fullPath]);
            return null;
        }

        // Get file content
        $imageContent = file_get_contents($fullPath);
        
        if ($imageContent === false) {
            Log::warning('Could not read local image file', ['path' => $fullPath]);
            return null;
        }

        // Get mime type from file extension
        $mimeType = self::getMimeTypeFromPath($fullPath);
        
        if (!$mimeType) {
            Log::warning('Could not determine mime type for local image', ['path' => $fullPath]);
            return null;
        }

        // Convert to base64
        $base64 = base64_encode($imageContent);
        
        return "data:{$mimeType};base64,{$base64}";
    }

    /**
     * Handle external image URLs
     */
    public static function handleExternalImage($imageUrl, $timeout)
    {
        // Validate URL
        if (!filter_var($imageUrl, FILTER_VALIDATE_URL)) {
            Log::warning('Invalid external image URL provided', ['url' => $imageUrl]);
            return null;
        }

        // Fetch the image using Laravel HTTP client
        $response = Http::timeout($timeout)->get($imageUrl);

        // Check if request was successful
        if (!$response->successful()) {
            Log::warning('Failed to fetch external image', [
                'url' => $imageUrl,
                'status' => $response->status()
            ]);
            return null;
        }

        // Get image content
        $imageContent = $response->body();
        
        // Check if content is not empty
        if (empty($imageContent)) {
            Log::warning('Empty external image content', ['url' => $imageUrl]);
            return null;
        }

        // Get content type from response headers
        $contentType = $response->header('Content-Type');
        
        // Validate that it's an image
        if (!$contentType || !str_starts_with($contentType, 'image/')) {
            Log::warning('External URL does not point to an image', [
                'url' => $imageUrl,
                'content_type' => $contentType
            ]);
            return null;
        }

        // Convert to base64
        $base64 = base64_encode($imageContent);
        
        return "data:{$contentType};base64,{$base64}";
    }

    /**
     * Get MIME type from file path/extension
     */
    public static function getMimeTypeFromPath($filePath)
    {
        $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
        
        $mimeTypes = [
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif',
            'webp' => 'image/webp',
            'svg' => 'image/svg+xml',
            'bmp' => 'image/bmp',
            'ico' => 'image/x-icon',
        ];

        return $mimeTypes[$extension] ?? null;
    }

    /** 
     * Generate Subscription Invoice
     */
    public static function generateSubscriptionInvoice($subscriptionId)
    {
        $subscription = UserSubscription::with('user:id,first_name,last_name,email','plan:id,name,interval,interval_count')
                ->where('id',$subscriptionId)
                ->first();

        if (!$subscription) {
            return response()->json([
                'status' => false,
                'message' => 'Data not found.'
            ]);
        }

        $stripe = new StripeClient(config('services.stripe.secret_key'));

        $invoice_number = $subscription->invoice_number;
        if (!$subscription->invoice_number && $subscription->plan_id != 1) {
            $invoices = $stripe->invoices->all([
                'subscription' => $subscription->stripe_subscription_id,
                'limit' => 1,
            ])->data[0] ?? null;
            
            $invoice_number = $invoices && isset($invoices['number']) && $invoices['number'] ? $invoices['number'] : null;
            $subscription->invoice_number = $invoice_number;
            $subscription->save();
        }

        $pdf = Pdf::loadView('content.pages.user-subscription.invoice',compact('subscription'))->setPaper('a4', 'portrait');

        $fileName = 'Invoice' . rand(0,999999999) . '.pdf';
        if ($invoice_number) {
            $fileName = 'Invoice' . $invoice_number . '.pdf';
        }

        return response($pdf->output(), 200, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => "attachment; filename=\"$fileName\"",
            'Cache-Control'       => 'no-store, no-cache, must-revalidate, max-age=0',
            'Pragma'              => 'no-cache',
        ]);
    }

    /**
     * Delete User's all Data
     * @param mixed $userId
     * @return bool
     */
    public static function deleteUserData($userId)
    {
        $user = User::find($userId);
        if (!$user) {
            return false;
        }
        try {
            DB::beginTransaction();

            // add fca number
            FcaNumbers::updateOrCreate([
                'fca_number' => $user->fca_number,
            ], [
                'fca_name' => $user->company_name,
            ]);
    
            // stock image delete
            // $user->imageStockManagement()->delete();
            $allDeleteImageData = ImageStockManagement::where('user_id', $userId)->get();
    
            foreach ($allDeleteImageData as $value) {
                Helpers::deleteImage($value->image_url);
                $value->delete();
            }
    
            // brand kit delete
            // $user->brandKit()->delete();
            $userBrandKit = BrandKit::where('user_id', $userId)->first();
            if (!empty($userBrandKit)) {
                Helpers::deleteImage($userBrandKit->logo);
                $userBrandKit->delete();
            }
    
            // subscription (all) delete
            // $user->subscription()->delete();
            $userSubscription = UserSubscription::where('user_id', $userId)->get();
            if (!empty($userSubscription)) {
                foreach ($userSubscription as $value) {
                    if ($value->plan_id != 1) {

                        if (!empty($value->stripe_subscription_id)) {
                            try {

                                $stripe = new StripeClient(config('services.stripe.secret_key'));

                                // Always fetch the subscription from Stripe
                                $stripeSub = $stripe->subscriptions->retrieve($value->stripe_subscription_id, []);

                                // Cancel if it's not already canceled
                                if ($stripeSub->status !== 'canceled') {
                                    // first cancel subscription from stripe
                                    $stripe->subscriptions->cancel($value->stripe_subscription_id, [
                                        'cancellation_details' => [
                                            'comment' => 'user deleted their account',
                                            // 'reason' => 'account_deleted',
                                        ],
                                    ]);
                                }

                            } catch (InvalidRequestException $e) {
                                // Most common: "No such subscription" (already deleted or never existed)
                                Log::warning("Stripe invalid request for subscription {$value->stripe_subscription_id} - {$value->id}: " . $e->getMessage());
                            } catch (ApiErrorException $e) {
                                // Any other Stripe API error (network, auth, etc.)
                                Log::error("Stripe API error for subscription {$value->stripe_subscription_id} - {$value->id}: " . $e->getMessage());
                            } catch (\Exception $e) {
                                // Catch-all for unexpected issues
                                Log::error("Unexpected error for subscription {$value->stripe_subscription_id} - {$value->id}: " . $e->getMessage());
                            }
                        }
    
                        $value->delete();
                    } else {
                        $value->delete();
                    }
                }
            }
    
            // user downloads
            $userDownloads = UserDownloads::where('user_id', $userId)->get();
            if (!empty($userDownloads)) {
                foreach ($userDownloads as $value) {
                    $value->delete();
                }
            }
    
            // user template delete
            $userTemplate = UserTemplates::where('user_id', $userId)->get();
            if (!empty($userTemplate)) {
                foreach ($userTemplate as $value) {
                    Helpers::deleteImage($value->template_image);
                    $value->delete();
                }
            }
    
            // delete usertokens
            $userTokens = UserTokens::where('user_id', $userId)->get();
            if (!empty($userTokens)) {
                foreach ($userTokens as $value) {
                    $value->delete();
                }
            }
    
            $user->delete();
            DB::commit();
            return true;
        } catch (Exception $e) {
            DB::rollBack();
            // dd($e);
            Log::error($e);
            Helpers::sendErrorMailToDeveloper($e,'User account delete API.');
            return false;
        }   
    }

    /** Email type list */
    public static function emailType()
    {
        return [
            'before_first_oct_mail' => "BEFORE 1st OCT",
            'after_first_oct_mail' => "FROM 1st OCT",
            'welcome_beta_trial' => "Welcome to Your Box Socials Beta Trial 🎉",
            'user_register_acount_in_review' => "Welcome Email -  ACCOUNT IN REVIEW",
            'user_register_acount_reviewed' => "Welcome Email -  ACCOUNT REVIEWED",
        ];
    }

    /** Send Dynamic Email Content */
    public static function sendDynamicContentEmail($data)
    {
        Mail::to($data['email'])->send(new DynamicContentMail($data));
    }

    /** Special Characters Replacements */
    public static function specialCharactersReplacments()
    {
        return [
            '&' => 'And',
            '<' => '',
            '>' => ''
        ];
    }
}
