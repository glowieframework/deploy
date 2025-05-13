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
     * Executes a command in the SSH remote server.
     * @param string $command Command to execute.
     * @param callable $callback Callback of the realtime result, receives the output as a parameter.
     * @return int Returns the process exit code.
     */
    public function exec(string $command, callable $callback)
    {
        // Wraps the shell command into SSH
        $command = escapeshellarg('bash -c ' . escapeshellarg($command));
        $command = "ssh -t -o StrictHostKeyChecking=no -o LogLevel=ERROR -p {$this->port} {$this->username}@{$this->host} {$command}";

        // Execute the command
        return Process::openShell($command, $callback);
    }
}
