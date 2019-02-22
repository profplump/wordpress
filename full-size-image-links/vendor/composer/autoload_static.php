<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit2605e89cef9fc84e4fbf6431ef455676
{
    public static $prefixLengthsPsr4 = array (
        'D' => 
        array (
            'DiDom\\' => 6,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'DiDom\\' => 
        array (
            0 => __DIR__ . '/..' . '/imangazaliev/didom/src/DiDom',
        ),
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit2605e89cef9fc84e4fbf6431ef455676::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit2605e89cef9fc84e4fbf6431ef455676::$prefixDirsPsr4;

        }, null, ClassLoader::class);
    }
}
