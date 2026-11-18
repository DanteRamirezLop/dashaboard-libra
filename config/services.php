<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
        'scheme' => 'https',
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],
    
    'turnstile' => [
        'site_key'   => env('TURNSTILE_SITE_KEY'),
        'secret_key' => env('TURNSTILE_SECRET_KEY'),
        'enable' => env('TURNSTILE_ENABLE'),
    ],

    'apiperu' => [
        'token'=> env('APIPERU_TOKEN'),
    ],
    
    'apiperutipocambio' => [
        'token'=> env('APIPERU_TIPOCAMBIO_TOKEN'),
    ],

    'dashboard' => [
        'token' => env('DASHBOARD_API_TOKEN'),
        'business_id' => env('DASHBOARD_BUSINESS_ID', 4),
    ],

    // Interruptor global de envío de SMS (Twilio, Nexmo u otro proveedor).
    // Deshabilitado por defecto; para reactivarlo poner SMS_ENABLED=true en el .env
    'sms' => [
        'enabled' => env('SMS_ENABLED', false),
    ],

    // URLs opcionales de monitoreo (ej. healthchecks.io). Se llaman cuando el job
    // termina bien; si un día no llega el ping, el servicio externo avisa.
    'scheduler_ping' => [
        'pos:calculateStatusLoan' => env('SCHEDULER_PING_MORA'),
        'pos:smsPagoVencer' => env('SCHEDULER_PING_SMS'),
        'pos:exchangerate' => env('SCHEDULER_PING_EXCHANGERATE'),
    ],

];
