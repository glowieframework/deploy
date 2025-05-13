<?php

namespace Glowie\Plugins\Deploy\Core;

use Exception;

/**
 * Deploy connections handler.
 * @category Plugin
 * @package glowieframework/deploy
 * @author Glowie
 * @copyright Copyright (c) Glowie
 * @license MIT
 * @link https://glowie.gabrielsilva.dev.br
 */
class Connections
{

    /**
     * List of open connections.
     * @var array
     */
    private static $connections = [];

    /**
     * Gets a connection.
     * @param string $name Server name.
     * @return mixed Connection resource if exists.
     */
    public static function get(string $name)
    {
        return self::$connections[$name] ?? null;
    }

    /**
     * Set a connection.
     * @param string $name Server name.
     * @param mixed $connection Connection resource.
     */
    public static function set(string $name, $connection)
    {
        self::$connections[$name] = $connection;
    }

    /**
     * Connects to a server.
     * @param string $serverName Name of the server.
     * @param array $serverInfo Associative array of info about the server.
     * @return mixed Connection resource if exists.
     */
    public static function connect(string $serverName, array $serverInfo)
    {
        // Checks if the connection already exists
        $connection = self::get($serverName);
        if ($connection) return $connection;

        // Creates the connection if not exists
        if (empty($connection)) {
            // Checks if its a local connection
            if (!empty($serverInfo['local'])) {
                self::set($serverName, new Local());
                return self::get($serverName);
            }

            // Validate SSH infos and connect to the server
            if (empty($serverInfo['host'])) throw new Exception("Missing host for server $serverName");
            $connection = new SSH($serverInfo['host'], $serverInfo['user'] ?? 'root', $serverInfo['port'] ?? 22);
            self::set($serverName, $connection);
        }

        // Returns the connection instance
        return self::get($serverName);
    }

    /**
     * Disconnects from a server.
     * @param string $serverName Server name to disconnect.
     */
    public static function disconnect(string $serverName)
    {
        unset(self::$connections[$serverName]);
    }

    /**
     * Disconnects from all servers.
     */
    public static function disconnectAll()
    {
        self::$connections = [];
    }
}
