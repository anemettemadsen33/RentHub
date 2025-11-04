<?php

return [
    'apps' => [
        [
            'id' => env('PUSHER_APP_ID'),
            'name' => env('APP_NAME'),
            'key' => env('PUSHER_APP_KEY'),
            'secret' => env('PUSHER_APP_SECRET'),
            'enable_client_messages' => false,
            'enable_statistics' => true,
        ],
    ],

    'host' => env('WEBSOCKET_HOST', '0.0.0.0'),
    'port' => env('WEBSOCKET_PORT', 6001),

    'ssl' => [
        'local_cert' => env('WEBSOCKET_SSL_LOCAL_CERT'),
        'local_pk' => env('WEBSOCKET_SSL_LOCAL_PK'),
        'passphrase' => env('WEBSOCKET_SSL_PASSPHRASE'),
    ],

    'max_request_size_in_kb' => 250,
    'channel_limits' => [
        'presence' => 100,
        'private' => 100,
        'public' => 100,
    ],

    'statistics' => [
        'model' => \BeyondCode\LaravelWebSockets\Statistics\Models\WebSocketsStatisticsEntry::class,
        'interval_in_seconds' => 60,
        'delete_statistics_older_than_days' => 60,
    ],
];
