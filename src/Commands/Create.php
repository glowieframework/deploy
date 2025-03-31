<?php

namespace Glowie\Plugins\Deploy\Commands;

use Glowie\Core\CLI\Command;
use Glowie\Core\CLI\Firefly;

/**
 * Command to create the tasks file.
 * @category Command
 * @package glowieframework/deploy
 * @author Glowie
 * @copyright Copyright (c) Glowie
 * @license MIT
 * @link https://glowie.gabrielsilva.dev.br
 */
class Create extends Command
{

    /**
     * The command handler.
     */
    public function run()
    {
        // Gets the tasks file path
        $path = $this->getArg('path', getcwd() . '/.deploy-tasks.php');
        if (is_file($path) && !$this->confirm(Firefly::color('WARNING: The tasks file already exists. Overwrite?', 'red'))) {
            return $this->warning('Operation cancelled.');
        }

        // Create the tasks file
        copy(__DIR__ . '/../Templates/tasks.php', $path);
        $this->success('Tasks file created successfully!');
        $this->info('File: ' . $path);
    }
}
