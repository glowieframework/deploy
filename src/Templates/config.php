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
                'host' => Env::get('DEPLOY_SSH_HOST'),
                'port' => Env::get('DEPLOY_SSH_PORT', 22),
                'user' => Env::get('DEPLOY_SSH_USER', 'root')
            ]
        ],

        // Notification settings
        'notifications' => [

            'discord' => Env::get('DEPLOY_DISCORD_URL'),

            'slack' => Env::get('DEPLOY_SLACK_URL'),

            'push' => Env::get('DEPLOY_PUSH_KEY'),

            'telegram' => [
                'bot_id' => Env::get('DEPLOY_TELEGRAM_BOT_ID'),
                'chat_id' => Env::get('DEPLOY_TELEGRAM_CHAT_ID')
            ]
        ]
    ]
];
