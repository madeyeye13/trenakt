<?php

use Stevebauman\Location\Drivers\GeoPlugin;
use Stevebauman\Location\Drivers\IpApi;
use Stevebauman\Location\Position;

return [

    /*
    |--------------------------------------------------------------------------
    | Driver
    |--------------------------------------------------------------------------
    |
    | The default driver you would like to use for location retrieval.
    |
    */

    'driver' => IpApi::class,

    /*
    |--------------------------------------------------------------------------
    | Driver Fallbacks
    |--------------------------------------------------------------------------
    |
    | The drivers you want to use to retrieve the user's location
    | if the above selected driver is unavailable.
    |
    | These will be called upon in order (first to last).
    |
    | Trimmed to a single fallback (GeoPlugin, also free/no token) instead
    | of four: Ip2locationio and IpInfo both require a token that isn't set
    | in .env (IP2LOCATIONIO_TOKEN / IPINFO_TOKEN), so they'd only ever
    | fail here anyway, and MaxMind needs a local database file that isn't
    | present either - each one still burns up to 'http.timeout' seconds
    | before failing. Worst case for registration is now 1 primary + 1
    | fallback instead of 1 primary + 4 fallbacks.
    |
    */

    'fallbacks' => [
        GeoPlugin::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Position
    |--------------------------------------------------------------------------
    |
    | Here you may configure the position instance that is created
    | and returned from the above drivers. The instance you
    | create must extend the built-in Position class.
    |
    */

    'position' => Position::class,

    /*
    |--------------------------------------------------------------------------
    | HTTP Client Options
    |--------------------------------------------------------------------------
    |
    | Here you may configure the options used by the underlying
    | Laravel HTTP client. This will be used in drivers that
    | request info via HTTP requests through API services.
    |
    | Lowered from 3s to 1.5s - combined with the trimmed fallback list
    | above, this caps the worst case (both drivers time out) at ~3
    | seconds instead of the previous ~15.
    |
    */

    'http' => [
        'timeout' => 1.5,
        'connect_timeout' => 1.5,
    ],

    /*
    |--------------------------------------------------------------------------
    | Localhost Testing
    |--------------------------------------------------------------------------
    |
    | If your running your website locally and want to test different
    | IP addresses to see location detection, set 'enabled' to true.
    |
    | The testing IP address is a Google host in the United-States.
    |
    | Left as-is (defaults to true via LOCATION_TESTING, which isn't set in
    | .env): on local dev, request()->ip() is normally a loopback address
    | (127.0.0.1) that no geolocation API could ever resolve, so this
    | substitution is what makes local testing possible at all - not a
    | bug. Turning it off locally wouldn't speed anything up; it would
    | just make every lookup fail after burning the full timeout instead.
    |
    */

    'testing' => [
        'ip' => '66.102.0.0',
        'enabled' => env('LOCATION_TESTING', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | MaxMind Configuration
    |--------------------------------------------------------------------------
    |
    | If web service is enabled, you must fill in your user ID and license key.
    |
    | If web service is disabled, it will try and retrieve the user's location
    | from the MaxMind database file located in the local path below.
    |
    | The MaxMind database file can be either City (default) or Country (smaller).
    |
    */

    'maxmind' => [
        'license_key' => env('MAXMIND_LICENSE_KEY'),

        'web' => [
            'enabled' => false,
            'user_id' => env('MAXMIND_USER_ID'),
            'locales' => ['en'],
            'options' => ['host' => 'geoip.maxmind.com'],
        ],

        'local' => [
            'type' => 'city',
            'path' => database_path('maxmind/GeoLite2-City.mmdb'),
            'url' => sprintf('https://download.maxmind.com/app/geoip_download_by_token?edition_id=GeoLite2-City&license_key=%s&suffix=tar.gz', env('MAXMIND_LICENSE_KEY')),
        ],
    ],

    'ip_api' => [
        'token' => env('IP_API_TOKEN'),
    ],

    'ipinfo' => [
        'token' => env('IPINFO_TOKEN'),
    ],

    'ipdata' => [
        'token' => env('IPDATA_TOKEN'),
    ],

    'ip2locationio' => [
        'token' => env('IP2LOCATIONIO_TOKEN'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Kloudend ~ ipapi.co Configuration
    |--------------------------------------------------------------------------
    |
    | The configuration for the Kloudend driver.
    |
    */

    'kloudend' => [

        'token' => env('KLOUDEND_TOKEN'),

    ],

];
