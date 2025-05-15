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
     * Shell environment variables.
     * @var array
     */
    private $env = [];

    /**
     * Sets the shell environment variables.
     * @param array $env Associative array of environment variables to expose.
     */
    public function setEnv(array $env)
    {
        $this->env = $env;
    }

    /**
     * Executes a command locally.
     * @param array $command Array of commands to execute.
     * @param callable $callback Callback of the realtime result, receives the output as a parameter.
     * @return int Returns the process exit code.
     */
    public function exec(array $command, callable $callback)
    {
        // Gets the current platform
        $isWindows = PHP_OS_FAMILY === 'Windows';

        // Parses the environment variables
        $env = [];
        foreach ($this->env as $key => $value) {
            if ($isWindows) {
                $env[] = sprintf('set "%s=%s"', $key, str_replace('"', '^"', $value));
            } else {
                $env[] = sprintf('export %s=%s', $key, escapeshellarg($value));
            }
        }

        // Wraps the shell command into heredoc
        if ($isWindows) {
            $env = !empty($env) ? (implode(' && ', $env) . ' && ') : '';
            $command = implode(' && ', $command);
            $command = sprintf('cmd /C "%s %s"', $env, str_replace('"', '^"', $command));
        } else {
            $env = !empty($env) ? (implode("\n", $env) . "\n") : '';
            $delimiter = 'EOF-GLOWIE-DEPLOY';
            $command = implode("\n", $command);
            $command = "bash -se << \\$delimiter\n" .
                $env .
                "set -e\n" .
                "$command\n" .
                $delimiter;
        }

        // Execute the command
        return Process::openShell($command, $callback);
    }
}
