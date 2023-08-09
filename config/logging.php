<?php

use Monolog\Handler\NullHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\SyslogUdpHandler;

return [

    /*
    |--------------------------------------------------------------------------
    | Default Log Channel
    |--------------------------------------------------------------------------
    |
    | This option defines the default log channel that gets used when writing
    | messages to the logs. The name specified in this option should match
    | one of the channels defined in the "channels" configuration array.
    |
    */

    'default' => env('LOG_CHANNEL', 'stack'),

    /*
    |--------------------------------------------------------------------------
    | Log Channels
    |--------------------------------------------------------------------------
    |
    | Here you may configure the log channels for your application. Out of
    | the box, Laravel uses the Monolog PHP logging library. This gives
    | you a variety of powerful log handlers / formatters to utilize.
    |
    | Available Drivers: "single", "daily", "slack", "syslog",
    |                    "errorlog", "monolog",
    |                    "custom", "stack"
    |
    */

    'channels' => [
        'seedling_change_requests' => [
            'driver' => 'single',
            'path' => storage_path('logs/seedling_change_requests.log'),
        ],
        'field_facilitators' => [
            'driver' => 'single',
            'path' => storage_path('logs/field_facilitators.log'),
        ],
        'farmer_training' => [
            'driver' => 'single',
            'path' => storage_path('logs/farmerTrainingDeleted.log'),
        ],
        'lahans' => [
            'driver' => 'single',
            'path' => storage_path('logs/lahans.log'),
        ],
        'lahan_umums' => [
            'driver' => 'single',
            'path' => storage_path('logs/lahan_umums.log'),
        ],
        'farmers' => [
            'driver' => 'single',
            'path' => storage_path('logs/farmers.log'),
        ],
        'planting_socializations' => [
            'driver' => 'single',
            'path' => storage_path('logs/planting_socializations.log'),
        ],
        'planting_holes' => [
            'driver' => 'single',
            'path' => storage_path('logs/planting_holes.log'),
        ],
        'material_organics' => [
            'driver' => 'single',
            'path' => storage_path('logs/material_organics.log'),
        ],
        'monitoring_1' => [
            'driver' => 'single',
            'path' => storage_path('logs/monitoring_1.log'),
        ],
        'distributions' => [
            'driver' => 'single',
            'path' => storage_path('logs/distributions.log'),
        ],
        'stack' => [
            'driver' => 'stack',
            'channels' => ['single'],
            'ignore_exceptions' => false,
        ],

        'single' => [
            'driver' => 'single',
            'path' => storage_path('logs/laravel.log'),
            'level' => 'debug',
        ],

        'daily' => [
            'driver' => 'daily',
            'path' => storage_path('logs/laravel.log'),
            'level' => 'debug',
            'days' => 14,
        ],

        'slack' => [
            'driver' => 'slack',
            'url' => env('LOG_SLACK_WEBHOOK_URL'),
            'username' => 'Laravel Log',
            'emoji' => ':boom:',
            'level' => 'critical',
        ],

        'papertrail' => [
            'driver' => 'monolog',
            'level' => 'debug',
            'handler' => SyslogUdpHandler::class,
            'handler_with' => [
                'host' => env('PAPERTRAIL_URL'),
                'port' => env('PAPERTRAIL_PORT'),
            ],
        ],

        'stderr' => [
            'driver' => 'monolog',
            'handler' => StreamHandler::class,
            'formatter' => env('LOG_STDERR_FORMATTER'),
            'with' => [
                'stream' => 'php://stderr',
            ],
        ],

        'syslog' => [
            'driver' => 'syslog',
            'level' => 'debug',
        ],

        'errorlog' => [
            'driver' => 'errorlog',
            'level' => 'debug',
        ],

        'null' => [
            'driver' => 'monolog',
            'handler' => NullHandler::class,
        ],

        'emergency' => [
            'path' => storage_path('logs/laravel.log'),
        ],
    ],

];
