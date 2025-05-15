<?php

namespace Glowie\Plugins\Deploy\Core;

use Glowie\Core\Exception\PluginException;

/**
 * Process shell command executor.
 * @category Plugin
 * @package glowieframework/deploy
 * @author Glowie
 * @copyright Copyright (c) Glowie
 * @license MIT
 * @link https://glowie.gabrielsilva.dev.br
 */
class Process
{
    /**
     * Opens a process shell and executes a command.
     * @param string $command Command to be executed.
     * @param callable $callback Callback of the realtime result, receives the output as a parameter.
     * @param string|null $input (Optional) Optional input string to send to the shell after opening.
     * @return int Returns the process exit code.
     */
    public static function openShell(string $command, callable $callback, ?string $input = null)
    {
        // Defines the pipes
        $pipes = [];
        $descriptorspec = [
            0 => ["pipe", "r"],
            1 => ["pipe", "w"],
            2 => ["pipe", "w"],
        ];

        // Creates the process resource
        $process = proc_open($command, $descriptorspec, $pipes);
        if (!is_resource($process)) throw new PluginException('Failed to start process');

        // Writes to the input if any
        if (!is_null($input)) {
            fwrite($pipes[0], $input);
            fclose($pipes[0]);
        } else {
            fclose($pipes[0]);
        }

        // Sets the read pipes as non-blocking
        stream_set_blocking($pipes[1], false);
        stream_set_blocking($pipes[2], false);

        // Reads the output line by line and send to the callback
        while (!feof($pipes[1]) || !feof($pipes[2])) {
            $read = [$pipes[1], $pipes[2]];
            $write = $except = null;
            stream_select($read, $write, $except, 0, 200000);

            foreach ($read as $stream) {
                $output = fread($stream, 4096);
                if ($output === false || $output === '') continue;
                call_user_func($callback, $output);
            }
        }

        // Closes the pipes
        foreach ($pipes as $pipe) {
            if (is_resource($pipe)) fclose($pipe);
        }

        // Gets and returns the exit code
        $status = proc_get_status($process);
        $exitCode = $status['exitcode'] ?? 0;
        proc_close($process);
        return $exitCode;
    }
}
