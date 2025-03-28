<?php

return [

    // Configurations for Glowie Deploy plugin
    'deploy' => [

        // Associative array with all the servers
        'servers' => [

            'localhost' => [
                'local' => true
            ],

            'web' => [
                'host' => env('SSH_HOST'),
                'port' => env('SSH_PORT', 22),
                'auth' => env('SSH_AUTH', 'password'),
                'username' => env('SSH_USER', 'root'),
                'password' => env('SSH_PASSWORD'),
            ]
        ],

        // Notification settings
        'notifications' => [

            'discord' => env('DISCORD_HOOK'),

            'slack' => env('SLACK_HOOK'),

            'alertzy' => env('ALERTZY_KEY'),

            'telegram' => [
                'bot_id' => env('TELEGRAM_BOT_ID'),
                'chat_id' => env('TELEGRAM_CHAT_ID')
            ]
        ]
    ]
];
