<?php

namespace App\Core;

use App\Repositories\SiteSettingsRepository;

class Site
{
    private static ?SiteSettingsRepository $repo = null;

    private static function getRepo(): SiteSettingsRepository
    {
        if (self::$repo === null) {
            self::$repo = new SiteSettingsRepository();
        }
        return self::$repo;
    }

    public static function name(): string
    {
        return self::getRepo()->get('site_name', 'NovaTrust');
    }

    public static function logoUrl(): string
    {
        return self::getRepo()->get('site_logo_url', '');
    }

    public static function faviconUrl(): string
    {
        return self::getRepo()->get('site_favicon_url', '/assets/images/icon-192.png');
    }

    public static function address(): string
    {
        return self::getRepo()->get('site_address', '123 Nova Way, Financial District, NY 10004');
    }

    public static function supportEmail(): string
    {
        return self::getRepo()->get('site_support_email', 'support@novatrust.com');
    }
}
