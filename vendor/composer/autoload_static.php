<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit175f515b2811e085f3258b59792b25af
{
    public static $prefixLengthsPsr4 = array (
        'P' => 
        array (
            'PhpOffice\\PhpWord\\' => 18,
            'PHPMailer\\PHPMailer\\' => 20,
        ),
        'L' => 
        array (
            'Laminas\\Escaper\\' => 16,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'PhpOffice\\PhpWord\\' => 
        array (
            0 => __DIR__ . '/..' . '/phpoffice/phpword/src/PhpWord',
        ),
        'PHPMailer\\PHPMailer\\' => 
        array (
            0 => __DIR__ . '/..' . '/phpmailer/phpmailer/src',
        ),
        'Laminas\\Escaper\\' => 
        array (
            0 => __DIR__ . '/..' . '/laminas/laminas-escaper/src',
        ),
    );

    public static $classMap = array (
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit175f515b2811e085f3258b59792b25af::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit175f515b2811e085f3258b59792b25af::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInit175f515b2811e085f3258b59792b25af::$classMap;

        }, null, ClassLoader::class);
    }
}
