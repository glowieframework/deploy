<?php

namespace Glowie\Plugins\Deploy\Commands;

use Glowie\Core\CLI\Command;
use Glowie\Plugins\Deploy\Core\Connections;

/**
 * Command to run the deploy tasks.
 * @category Command
 * @package glowieframework/deploy
 * @author Glowie
 * @copyright Copyright (c) Glowie
 * @license MIT
 * @link https://glowie.gabrielsilva.dev.br
 */
class Run extends Command
{

    /**
     * The command handler.
     */
    public function run()
    {
        // Gets the tasks file path
        $path = $this->getArg('path', rtrim(getcwd(), '/') . '/.deploy-tasks.php');
        if (!is_file($path)) {
            $this->fail("[Deploy] Tasks file \"$path\" does not exist");
            exit(127);
        }

        // Gets the task name
        $task = $this->getArg('task', 'deploy');

        // Loads the tasks file
        $tasks = require_once($path);

        // Checks if the task exists
        if (is_callable([$tasks, $task])) {
            // Calls the init method if exists
            if (is_callable([$tasks, 'init'])) $tasks->init($task);

            try {
                // Calls the task
                $tasks->{$task}();
                $tasks->__processCommands();
            } catch (\Throwable $th) {
                // On failure, calls the fail method if exists
                if (is_callable([$tasks, 'fail'])) $tasks->fail($task, $th);
                $this->fail("[Deploy] Task \"$task\" failed with message: " . $th->getMessage());
                exit($th->getCode() ? $th->getCode() : 127);
            }

            // On success, calls the done method if exists
            if (is_callable([$tasks, 'done'])) $tasks->done($task);
            $this->success("[Deploy] Task \"$task\" finished successfully");
        } else {
            $this->fail("[Deploy] Task \"$task\" does not exist in the tasks file");
            exit(127);
        }

        // End all connections
        Connections::disconnectAll();
    }
}
