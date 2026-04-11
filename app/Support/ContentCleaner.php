<?php

namespace App\Support;

use HTMLPurifier;
use HTMLPurifier_Config;

class ContentCleaner
{
    private static ?HTMLPurifier $instance = null;

    public static function clean(?string $dirty): string
    {
        if (! $dirty) {
            return '';
        }

        return self::instance()->purify($dirty);
    }

    private static function instance(): HTMLPurifier
    {
        if (self::$instance !== null) {
            return self::$instance;
        }

        $dir = storage_path('app/htmlpurifier');
        if (! is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $config = HTMLPurifier_Config::createDefault();
        $config->set('Cache.SerializerPath', $dir);
        $config->set('HTML.Allowed',
            'h1,h2,h3,h4,h5,h6,p,br,strong,em,b,i,u,'.
            'a[href|title|target|rel],'.
            'ul,ol,li,blockquote,pre,code,'.
            'img[src|alt|width|height],'.
            'table,thead,tbody,tr,th,td,hr,'.
            'span[class],div[class],sup,sub'
        );
        $config->set('HTML.TargetBlank', true);
        $config->set('AutoFormat.RemoveEmpty', true);
        $config->set('URI.AllowedSchemes', ['http' => true, 'https' => true, 'mailto' => true]);
        $config->set('Attr.AllowedFrameTargets', ['_blank']);
        $config->set('Attr.EnableID', false);

        return self::$instance = new HTMLPurifier($config);
    }
}
