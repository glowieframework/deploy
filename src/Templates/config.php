<?php

/*
    ---------------------------
    Deploy configuration
    ---------------------------
    In this file you can define your deploy servers and notification settings.

    -----------------------------------------------------------------------------------------
    WARNING: Sensitive data should never be stored here! Use the environment config instead.
    -----------------------------------------------------------------------------------------
*/

return [

    // Configurations for Glowie Deploy plugin
    'deploy' => [

        // Associative array with all the servers
        'servers' => [

            'localhost' => [
                'local' => true
            ],

            'web' => [
                'host' => env('DEPLOY_SSH_HOST'),
                'port' => env('DEPLOY_SSH_PORT', 22),
                'auth' => env('DEPLOY_SSH_AUTH', 'password'),
                'username' => env('DEPLOY_SSH_USER', 'root'),
                'password' => env('DEPLOY_SSH_PASSWORD'),
            ]
        ],

        // Notification settings
        'notifications' => [

            'discord' => env('DEPLOY_DISCORD_URL'),

            'slack' => env('DEPLOY_SLACK_URL'),

            'push' => env('DEPLOY_PUSH_KEY'),

            'telegram' => [
                'bot_id' => env('DEPLOY_TELEGRAM_BOT_ID'),
                'chat_id' => env('DEPLOY_TELEGRAM_CHAT_ID')
            ]
        ]
    ]
];
