<?php

return [
    'deploy' => [
        'servers' => [
            'localhost' => [
                'host' => Env::get('SSH_HOST', 'localhost'),
                'port' => Env::get('SSH_PORT', 22),
                'auth' => Env::get('SSH_AUTH', 'password'),
                'username' => Env::get('SSH_USER', 'root'),
                'password' => Env::get('SSH_PASSWORD'),
            ]
        ]
    ]
];
