<?php

namespace Glowie\Plugins\Deploy\Commands;

use Exception;
use Glowie\Core\CLI\Command;

class Run extends Command
{

    /**
     * The command handler.
     */
    public function run()
    {
        // Get the tasks file path
        $path = $this->getArg('path', getcwd() . '/.deploy-tasks.php');
        if (!is_file($path)) throw new Exception("Tasks file \"$path\" does not exist");

        // Get the story name
        $story = $this->getArg('story', 'deploy');

        // Load the tasks file
        $tasks = require_once($path);
        if (is_callable([$tasks, 'init'])) $tasks->init();

        // Call the story
        if (is_callable([$tasks, $story])) {
            $tasks->{$story}();
        } else {
            throw new Exception("Story \"$story\" does not exist in the tasks file");
        }

        // End all connections
        $tasks->disconnectAll();
    }
}
