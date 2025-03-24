<?php

return [

    // Configurations for Glowie Deploy plugin
    'deploy' => [

        // Array with all the servers
        'servers' => [

            'localhost' => [
                'local' => true
            ],

            'web' => [
                'host' => Env::get('SSH_HOST', 'localhost'),
                'port' => Env::get('SSH_PORT', 22),
                'auth' => Env::get('SSH_AUTH', 'password'),
                'username' => Env::get('SSH_USER', 'root'),
                'password' => Env::get('SSH_PASSWORD'),
            ]
        ],

        // Notification settings
        'notifications' => [

            'discord' => Env::get('DISCORD_HOOK'),

            'telegram' => [
                'bot_id' => Env::get('TELEGRAM_BOT_ID'),
                'chat_id' => Env::get('TELEGRAM_CHAT_ID')
            ]
        ]
    ]
];
