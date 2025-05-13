<?php

namespace Glowie\Plugins\Deploy\Core;

/**
 * Deploy local commands handler.
 * @category Plugin
 * @package glowieframework/deploy
 * @author Glowie
 * @copyright Copyright (c) Glowie
 * @license MIT
 * @link https://glowie.gabrielsilva.dev.br
 */
class Local
{
    /**
     * Executes a command locally.
     * @param string $command Command to execute.
     * @param callable $callback Callback of the realtime result, receives the output as a parameter.
     * @return int Returns the process exit code.
     */
    public function exec(string $command, callable $callback)
    {
        // Wraps the shell command
        if (stripos(PHP_OS, 'WIN') === 0) {
            $command = 'cmd /c ' . escapeshellarg($command);
        } else {
            $command = 'bash -c ' . escapeshellarg($command);
        }

        // Execute the command
        return Process::openShell($command, $callback);
    }
}
