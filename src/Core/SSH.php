<?php

namespace Glowie\Plugins\Deploy\Core;

use Exception;

class SSH
{
    private $connection;
    private $host;
    private $port;

    public function __construct(string $host, int $port = 22)
    {
        $this->host = $host;
        $this->port = $port;
        $this->connection = @ssh2_connect($host, $port);

        if (!$this->connection) {
            throw new Exception("Failed to connect to SSH server at $host:$port");
        }
    }

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

    public function authenticateKeys(string $username, string $publicKey, string $privateKey, ?string $passphrase)
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

    public function disconnect()
    {
        if (!$this->connection) {
            throw new Exception('SSH connection was not established');
        }

        @ssh2_disconnect($this->connection);
        $this->connection = null;
    }

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

    private function getOutput($stream, $id)
    {
        $streamOut = @ssh2_fetch_stream($stream, $id);
        return stream_get_contents($streamOut);
    }
}
