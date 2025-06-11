<?php

namespace App\Helpers;

use App\Events\NewNotificationEvent;
use App\Mail\RegisterVerificationMail;
use App\Models\Notification;
use App\Models\UserTokens;
use App\Notifications\CustomVerifyEmail;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Pusher\Pusher;

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
    public static function uploadImage($prefix, $image, $path)
    {
        // Ensure the directory exists in the storage
        if (!Storage::disk('public')->exists($path)) {
            Storage::disk('public')->makeDirectory($path);
        }
        // Generate a unique name for the image
        $image_name = $prefix . "_" . time() . '.' . $image->getClientOriginalExtension();
        
        // Store the image in the storage directory
        $image->storeAs($path, $image_name, 'public');
        // Return the relative path to the image
        $imageUrl = 'storage/' . $path . '/' . $image_name;
        return $imageUrl;
    }
    
    /**
     * Function to delete image
     * @param mixed $path
     * @return void
     */
    public static function deleteImage($path)
    {
        try {
            // Remove 'storage/' prefix if it exists
            $storagePath = str_replace('storage/', '', $path);
            
            if (Storage::disk('public')->exists($storagePath)) {
                Storage::disk('public')->delete($storagePath);
                Log::info("File deleted successfully: " . $path);
            } else {
                Log::info("File does not exist at: " . $path);
            }
        } catch (\Exception $e) {
            Log::error("Error deleting file: " . $e->getMessage());
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
            // Validate URL
            if (!filter_var($imageUrl, FILTER_VALIDATE_URL)) {
                throw new \Exception('Invalid URL provided');
            }

            // Ensure the directory exists in the storage
            if (!Storage::disk('public')->exists($path)) {
                Storage::disk('public')->makeDirectory($path);
            }

            // Get image content from URL
            $imageContent = @file_get_contents($imageUrl);
            
            if ($imageContent === false) {
                throw new \Exception('Failed to fetch image from URL');
            }

            // Get image info to determine extension
            $imageInfo = @getimagesizefromstring($imageContent);
            
            if ($imageInfo === false) {
                throw new \Exception('Invalid image format');
            }

            // Determine file extension from MIME type
            $extension = self::getExtensionFromMimeType($imageInfo['mime']);
            
            if (!$extension) {
                throw new \Exception('Unsupported image format');
            }

            // Generate a unique name for the image
            $image_name = $prefix . "_" . time() . '.' . $extension;
            
            // Store the image content in the storage directory
            $fullPath = $path . '/' . $image_name;
            Storage::disk('public')->put($fullPath, $imageContent);
            
            // Return the relative path to the image
            $savedImageUrl = 'storage/' . $fullPath;
            return $savedImageUrl;

        } catch (\Exception $e) {
            // Log the error
            Log::error('Image upload from URL failed: ' . $e->getMessage());
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
    public static function generateVarificationToken($user,Request $request)
    {
        $tokenData = bin2hex(random_bytes(32));

        $userToken = new UserTokens();
        $userToken->user_id = $user->id;
        $userToken->token = $tokenData;
        $userToken->type = 'email-verification';
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
        switch ($type) {
            case 'new-registration':
                $fullName = $data->first_name." ".$data->last_name;
                $title = "New Registration";
                $body = "New user ".$fullName." registered";
                break;
            case 'new-contact-us':
                $title = "New Feedback";
                $body = "New feedback is given by ".$data->name;
                break;
            default:
                $title = "New Notification";
                $body = "New notification is submitted";
                break;
        }

        if($title && $body){
            $newnotification = Notification::create([
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
}
