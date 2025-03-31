<?php

namespace Glowie\Plugins\Deploy\Core;

use Exception;

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
     * The connection handler.
     * @var resource|null
     */
    private $connection;

    /**
     * Server hostname.
     * @var string
     */
    private $host;

    /**
     * Server port.
     * @var int
     */
    private $port;

    /**
     * Creates a new SSH connection.
     * @param string $host Server hostname.
     * @param int $port (Optional) Server port.
     */
    public function __construct(string $host, int $port = 22)
    {
        // Validate extension
        if (!extension_loaded('ssh2')) {
            throw new Exception('Missing ssh2 extension in your PHP installation');
        }

        // Create connection
        $this->host = $host;
        $this->port = $port;
        $this->connection = @ssh2_connect($host, $port);

        if (!$this->connection) {
            throw new Exception("Failed to connect to SSH server at $host:$port");
        }
    }

    /**
     * Authenticates over SSH using username and password.
     * @param string $username Username to authenticate.
     * @param string $password Password to authenticate.
     * @return bool Returns true on success, false otherwise.
     */
    public function authenticate(string $username, string $password)
    {
        if (!$this->connection) {
            throw new Exception('SSH connection was not established');
        }

        $result = @ssh2_auth_password($this->connection, $username, $password);

        if (!$result) {
            throw new Exception("Failed to authenticate with username \"$username\" and password on server {$this->host}:{$this->port}");
            return false;
        }

        return true;
    }

    /**
     * Authenticates over SSH using a pair of keys.
     * @param string $username Username to authenticate.
     * @param string $publicKey Public key file path.
     * @param string $privateKey Private key file path.
     * @param string|null $passphrase (Optional) Passphrase if the files are encrypted.
     * @return bool Returns true on success, false otherwise.
     */
    public function authenticateKeys(string $username, string $publicKey, string $privateKey, ?string $passphrase = null)
    {
        if (!$this->connection) {
            throw new Exception('SSH connection was not established');
        }

        $result = @ssh2_auth_pubkey_file($this->connection, $username, $publicKey, $privateKey, $passphrase);

        if (!$result) {
            throw new Exception("Failed to authenticate with username \"$username\" and public key on server {$this->host}:{$this->port}");
            return false;
        }

        return true;
    }

    /**
     * Authenticates over SSH using ssh-agent
     * @param string $username Username to authenticate.
     * @return bool Returns true on success, false otherwise.
     */
    public function authenticateAgent(string $username)
    {
        if (!$this->connection) {
            throw new Exception('SSH connection was not established');
        }

        $result = @ssh2_auth_agent($this->connection, $username);

        if (!$result) {
            throw new Exception("Failed to authenticate with username \"$username\" and agent on server {$this->host}:{$this->port}");
            return false;
        }

        return true;
    }

    /**
     * Disconnects from the server.
     */
    public function disconnect()
    {
        if (!$this->connection) {
            throw new Exception('SSH connection was not established');
        }

        @ssh2_disconnect($this->connection);
        $this->connection = null;
    }

    /**
     * Executes a command in the server.
     * @param string $command Command to run.
     * @param string|null &$stdErr Variable to store the errors, if any.
     * @return mixed Returns the command output.
     */
    public function exec(string $command, &$stdErr = null)
    {
        if (!$this->connection) {
            throw new Exception('SSH connection was not established');
        }

        $stream = ssh2_exec($this->connection, $command);
        if (!$stream) return null;

        stream_set_blocking($stream, true);

        $stdIo = $this->getOutput($stream, SSH2_STREAM_STDIO);
        $stdErr = $this->getOutput($stream, SSH2_STREAM_STDERR);

        stream_set_blocking($stream, false);

        return $stdIo;
    }

    /**
     * Gets a stream output.
     * @param resource $stream Stream to get.
     * @param int $id Stream ID.
     * @return mixed Returns the output.
     */
    private function getOutput($stream, int $id)
    {
        $streamOut = @ssh2_fetch_stream($stream, $id);
        return stream_get_contents($streamOut);
    }
}
