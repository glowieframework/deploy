<?php

namespace Glowie\Plugins\Deploy\Core;

/**
 * Deploy SSH commands handler.
 * @category Plugin
 * @package glowieframework/deploy
 * @author Glowie
 * @copyright Copyright (c) Glowie
 * @license MIT
 * @link https://glowie.gabrielsilva.dev.br
 */
class SSH
{
    /**
     * Server hostname.
     * @var string
     */
    private $host;

    /**
     * Server username.
     * @var string
     */
    private $username;

    /**
     * Server port.
     * @var int
     */
    private $port;

    /**
     * Shell environment variables.
     * @var array
     */
    private $env = [];

    /**
     * Creates a new SSH connection.
     * @param string $host Server hostname.
     * @param string $username (Optional) Server username.
     * @param int $port (Optional) Server port.
     */
    public function __construct(string $host, string $username = 'root', int $port = 22)
    {
        $this->host = $host;
        $this->username = $username;
        $this->port = $port;
    }

    /**
     * Sets the shell environment variables.
     * @param array $env Associative array of environment variables to expose.
     */
    public function setEnv(array $env)
    {
        $this->env = $env;
    }

    /**
     * Executes a command in the SSH remote server.
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
            if ($value === false) continue;
            $env[] = 'export ' . $key . '="' . $value . '"';
        }

        // Defines the SSH connection command
        $delimiter = 'EOF-GLOWIE-DEPLOY';
        $ssh = "ssh -t -o StrictHostKeyChecking=no -o LogLevel=ERROR -p {$this->port} {$this->username}@{$this->host}";
        $command = implode(PHP_EOL, $command);

        // Checks for the platform
        if ($isWindows) {
            // Wraps the shell command to the shell input
            $input = 'bash -se' . PHP_EOL .
                (!empty($env) ? implode(PHP_EOL, $env) . PHP_EOL : '') .
                'set -e' . PHP_EOL .
                $command;

            $command = "cmd /C \"$ssh\"";

            // Execute the command with the input
            return Process::openShell($command, $callback, str_replace("\r", '', $input));
        } else {
            // Wraps the shell command into heredoc with SSH
            $command = "$ssh 'bash -se' << \\$delimiter" . PHP_EOL .
                (!empty($env) ? implode(PHP_EOL, $env) . PHP_EOL : '') .
                'set -e' . PHP_EOL .
                $command . PHP_EOL .
                $delimiter;

            // Execute the command
            return Process::openShell($command, $callback);
        }
    }
}
