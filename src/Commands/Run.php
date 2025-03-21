<?php

namespace Glowie\Plugins\Deploy\Commands;

use Exception;
use Glowie\Core\CLI\Command;
use Glowie\Plugins\Deploy\Core\Connections;

class Run extends Command
{

    /**
     * The command handler.
     */
    public function run()
    {
        // Gets the tasks file path
        $path = $this->getArg('path', getcwd() . '/.deploy-tasks.php');
        if (!is_file($path)) throw new Exception("Tasks file \"$path\" does not exist");

        // Gets the task name
        $task = $this->getArg('task', 'deploy');

        // Loads the tasks file
        $tasks = require_once($path);

        // Checks if the task exists
        if (is_callable([$tasks, $task])) {
            // Calls the init method if exists
            if (is_callable([$tasks, 'init'])) $tasks->init();

            try {
                // Calls the task
                $tasks->{$task}();
            } catch (\Throwable $th) {
                // On failure, calls the fail method if exists
                if (is_callable([$tasks, 'fail'])) $tasks->fail($task, $th);
                throw new Exception("[$task] " . $th->getMessage(), $th->getCode(), $th);
            }

            // On success, calls the success method if exists
            if (is_callable([$tasks, 'success'])) $tasks->success($task);
        } else {
            throw new Exception("Task \"$task\" does not exist in the tasks file");
        }

        // End all connections
        Connections::disconnectAll();
    }
}
