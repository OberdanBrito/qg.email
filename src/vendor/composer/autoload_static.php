<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInitecd0911383977dab835ebb971ceb6502
{
    public static $prefixLengthsPsr4 = array (
        'c' => 
        array (
            'craos\\' => 6,
        ),
        'P' => 
        array (
            'PHPMailer\\PHPMailer\\' => 20,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'craos\\' => 
        array (
            0 => __DIR__ . '/../..' . '/src',
        ),
        'PHPMailer\\PHPMailer\\' => 
        array (
            0 => __DIR__ . '/..' . '/phpmailer/phpmailer/src',
        ),
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInitecd0911383977dab835ebb971ceb6502::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInitecd0911383977dab835ebb971ceb6502::$prefixDirsPsr4;

        }, null, ClassLoader::class);
    }
}
