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
     * @return int Returns the process exit code.
     */
    public static function openShell(string $command, callable $callback)
    {
        $pipes = [];
        $process = null;

        $descriptorspec = [
            0 => ["pipe", "r"],
            1 => ["pipe", "w"],
            2 => ["pipe", "w"],
        ];

        $process = proc_open($command, $descriptorspec, $pipes);

        if (!is_resource($process)) throw new PluginException('Failed to start process');

        stream_set_blocking($pipes[1], false);
        stream_set_blocking($pipes[2], false);

        fclose($pipes[0]);

        while (!feof($pipes[1]) || !feof($pipes[2])) {
            $read = [$pipes[1], $pipes[2]];
            $write = $except = null;

            stream_select($read, $write, $except, 0, 200000);

            foreach ($read as $stream) {
                $output = fread($stream, 4096);
                if ($output === false || $output === '') continue;
                if ($stream === $pipes[1]) {
                    call_user_func($callback, $output);
                } elseif ($stream === $pipes[2]) {
                    call_user_func($callback, $output);
                }
            }
        }

        foreach ($pipes as $pipe) {
            if (is_resource($pipe)) fclose($pipe);
        }

        $status = proc_get_status($process);
        $exitCode = $status['exitcode'] ?? 0;

        proc_close($process);

        return $exitCode;
    }
}
