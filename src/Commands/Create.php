<?php

namespace Glowie\Plugins\Deploy\Commands;

use Glowie\Core\CLI\Command;

class Create extends Command
{

    /**
     * The command handler.
     */
    public function run()
    {
        // Gets the tasks file path
        $path = $this->getArg('path', getcwd() . '/.deploy-tasks.php');
        if (is_file($path) && !$this->confirm('<color="red">WARNING: The tasks file already exists. Overwrite?</color>')) {
            return $this->warning('Opperation cancelled.');
        }

        // Create the tasks file
        copy(realpath(__DIR__ . '/../Templates/.deploy-tasks.php'), $path);
        $this->success('Tasks file created successfully!');
        $this->info('File: ' . $path);
    }
}
