<?php

namespace Glowie\Plugins\Deploy\Commands;

use Glowie\Core\CLI\Command;
use Glowie\Core\CLI\Firefly;
use Util;

/**
 * Command to create the config file.
 * @category Command
 * @package glowieframework/deploy
 * @author Glowie
 * @copyright Copyright (c) Glowie
 * @license MIT
 * @link https://glowie.gabrielsilva.dev.br
 */
class Config extends Command
{

    /**
     * The command handler.
     */
    public function run()
    {
        // Sets the default config path
        if (file_exists(Util::location('config'))) {
            $defaultPath = Util::location('config/Deploy.php');
        } else {
            $defaultPath = rtrim(getcwd(), '/') . '/config.php';
        }

        // Gets the config file path
        $path = $this->getArg('path', $defaultPath);
        if (is_file($path) && !$this->confirm(Firefly::color('WARNING: The config file already exists. Overwrite?', 'red'))) {
            return $this->warning('Operation cancelled.');
        }

        // Create the config file
        copy(__DIR__ . '/../Templates/config.php', $path);
        $this->success('[Deploy] Config file created successfully!');
        $this->info('[Deploy] File: ' . $path);
    }
}
