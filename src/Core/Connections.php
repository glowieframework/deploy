<?php

namespace Glowie\Plugins\Deploy\Core;

use Exception;

class Connections
{

    private static $connections = [];

    public static function get(string $name)
    {
        return self::$connections[$name] ?? null;
    }

    public static function set(string $name, $connection)
    {
        self::$connections[$name] = $connection;
    }

    public static function connect(string $serverName, array $serverInfo)
    {
        // Checks if the connection already exists
        $connection = self::get($serverName);
        if ($connection) return $connection;

        // Creates the connection if not exists
        if (empty($connection)) {
            // Validate infos
            if (empty($serverInfo['host'])) throw new Exception("Missing host for server $serverName");
            if (empty($serverInfo['username'])) throw new Exception("Missing username for server $serverName");

            // Connect to the server
            $connection = new SSH($serverInfo['host'], $serverInfo['port'] ?? 22);

            // Checks for authentication method
            if (!empty($serverInfo['auth']) && $serverInfo['auth'] === 'password') {
                // Validate info
                if (empty($serverInfo['password'])) throw new Exception("Missing password for server $serverName");

                // Authenticate with password
                if ($connection->authenticate($serverInfo['username'], $serverInfo['password'])) {
                    self::set($serverName, $connection);
                }
            } else if ($serverInfo['auth'] === 'public_key') {
                // Validate infos
                if (empty($serverInfo['public_key'])) throw new Exception("Missing public key file for server $serverName");
                if (empty($serverInfo['private_key'])) throw new Exception("Missing private key file for server $serverName");

                // Authenticate with key pair
                if ($connection->authenticateKeys($serverInfo['username'], $serverInfo['public_key'], $serverInfo['private_key'], $serverInfo['passphrase'] ?? null)) {
                    self::set($serverName, $connection);
                }
            } else if ($serverInfo['auth'] === 'agent') {
                // Authenticate with ssh agent
                if ($connection->authenticateAgent($serverInfo['username'])) {
                    self::set($serverName, $connection);
                }
            } else {
                self::set($serverName, $connection);
            }
        }

        // Returns the connection instance
        return self::get($serverName);
    }
}
