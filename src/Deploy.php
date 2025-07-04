<?php

namespace Glowie\Plugins\Deploy;

use Glowie\Core\CLI\Firefly;
use Glowie\Core\Plugin;
use Glowie\Plugins\Deploy\Commands\Config;
use Glowie\Plugins\Deploy\Commands\Create;
use Glowie\Plugins\Deploy\Commands\Run;
use Glowie\Plugins\Deploy\Commands\Story;

/**
 * Glowie plugin to deploy applications with SSH.
 * @category Plugin
 * @package glowieframework/deploy
 * @author Glowie
 * @copyright Copyright (c) Glowie
 * @license MIT
 * @link https://glowie.gabrielsilva.dev.br
 */
class Deploy extends Plugin
{

    /**
     * Array of files and directories to be published to the app folder.
     * @var array
     */
    protected $files = [
        __DIR__ . '/Templates/config.php' => 'config/Deploy.php',
        __DIR__ . '/Templates/tasks.php' => '../.deploy-tasks.php'
    ];

    /**
     * Initializes the plugin.
     */
    public function register()
    {
        Firefly::custom('deploy', Run::class);
        Firefly::custom('deploy', Story::class);
        Firefly::custom('deploy', Create::class);
        Firefly::custom('deploy', Config::class);
    }
}
