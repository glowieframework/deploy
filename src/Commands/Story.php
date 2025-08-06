<?php

namespace Glowie\Plugins\Deploy\Commands;

use Glowie\Core\CLI\Command;
use Glowie\Plugins\Deploy\Core\Connections;

/**
 * Command to run a deploy story.
 * @category Command
 * @package glowieframework/deploy
 * @author Glowie
 * @copyright Copyright (c) Glowie
 * @license MIT
 * @link https://glowie.gabrielsilva.dev.br
 */
class Story extends Command
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

        // Gets the story name
        $story = $this->getArg('name', 'pipeline');

        // Loads the tasks file
        $tasksClass = require_once($path);

        // Calls the story method
        try {
            $tasksClass->story($story);
        } catch (\Throwable $th) {
            $this->fail($th->getMessage());
            exit($th->getCode() ? $th->getCode() : 127);
        } finally {
            // End all connections
            Connections::disconnectAll();
        }
    }
}
