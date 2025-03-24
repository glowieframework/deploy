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
                'host' => Env::get('SSH_HOST'),
                'port' => Env::get('SSH_PORT', 22),
                'auth' => Env::get('SSH_AUTH', 'password'),
                'username' => Env::get('SSH_USER', 'root'),
                'password' => Env::get('SSH_PASSWORD'),
            ]
        ],

        // Notification settings
        'notifications' => [

            'discord' => Env::get('DISCORD_HOOK'),

            'slack' => Env::get('SLACK_HOOK'),

            'alertzy' => Env::get('ALERTZY_KEY'),

            'telegram' => [
                'bot_id' => Env::get('TELEGRAM_BOT_ID'),
                'chat_id' => Env::get('TELEGRAM_CHAT_ID')
            ]
        ]
    ]
];
