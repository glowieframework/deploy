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
            $this->fail("Tasks file \"$path\" does not exist");
            exit(127);
        }

        // Gets the task name
        $task = $this->getArg('task', 'deploy');

        // Loads the tasks file
        $tasksClass = require_once($path);

        // Calls the task method
        try {
            $tasksClass->task($task);
        } catch (\Throwable $th) {
            $this->fail($th->getMessage());
            exit($th->getCode() ? $th->getCode() : 127);
        } finally {
            // End all connections
            Connections::disconnectAll();
        }
    }
}
