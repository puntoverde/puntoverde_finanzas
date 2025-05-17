<?php
namespace App\Util;
use Firebase\JWT\JWT;
// use Laravel\Lumen\Bootstrap\LoadEnvironmentVariables;

class Auth
{
    // (new Laravel\Lumen\Bootstrap\LoadEnvironmentVariables(
    //     dirname(__DIR__)
    // ))->bootstrap();
    //private static $secret_key = 'GQDstcKsx0NHjPOuXOYg5MbeJ1XT0uFiwDVvVBrk123456789';
    
    private static $secret_key;
    
    private static $encrypt = ['HS256'];
    private static $aud = null;

    public function __construct()
    {
        self::$secret_key=env('APP_KEY', 'GQDstcKsx0NHjPOuXOYg5MbeJ1XT0uFiwDVvVBrk123456789');
    }

    public static function SignIn($data)
    {
        self::$secret_key=env('APP_KEY', 'GQDstcKsx0NHjPOuXOYg5MbeJ1XT0uFiwDVvVBrk123456789');
        $time = time();

        $token = array(
            'exp' => $time + (60*60),
            'aud' => self::Aud(),
            'data' => $data
        );

        return JWT::encode($token, self::$secret_key);
    }

    public static function Check($token)
    { self::$secret_key=env('APP_KEY', 'GQDstcKsx0NHjPOuXOYg5MbeJ1XT0uFiwDVvVBrk123456789');
        if(empty($token))
        {
            throw new \Exception("Invalid token supplied.");
        }

        $decode = JWT::decode(
            $token,
            self::$secret_key,
            self::$encrypt
        );

        // if($decode->aud !== self::Aud())
        // {
        //     throw new \Exception("Invalid user logged in.");
        // }
    }

    public static function GetData($token)
    { self::$secret_key=env('APP_KEY', 'GQDstcKsx0NHjPOuXOYg5MbeJ1XT0uFiwDVvVBrk123456789');
        return JWT::decode(
            $token,
            self::$secret_key,
            self::$encrypt
        )->data;
    }

    private static function Aud()
    {
        $aud = '';

        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $aud = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $aud = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            $aud = $_SERVER['REMOTE_ADDR'];
        }

        $aud .= @$_SERVER['HTTP_USER_AGENT'];
        $aud .= gethostname();

        return sha1($aud);
    }
}
