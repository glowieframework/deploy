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
     * @param string $command Command to execute.
     * @param callable $callback Callback of the realtime result, receives the output as a parameter.
     * @return int Returns the process exit code.
     */
    public function exec(string $command, callable $callback)
    {
        // Parses the environment variables
        $env = [];
        foreach ($this->env as $key => $value) {
            if ($value === false) continue;
            $env[] = 'export ' . $key . '="' . $value . '"';
        }

        // Wraps the shell command into heredoc
        $delimiter = 'EOF-GLOWIE-DEPLOY';
        $command = "bash -se << \\$delimiter" . PHP_EOL .
            (!empty($env) ? implode(PHP_EOL, $env) . PHP_EOL : '') .
            'set -e' . PHP_EOL .
            $command . PHP_EOL .
            $delimiter;

        // Execute the command
        return Process::openShell($command, $callback);
    }
}
