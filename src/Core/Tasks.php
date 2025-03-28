<?php

namespace Glowie\Plugins\Deploy\Core;

use Exception;
use Glowie\Core\CLI\Firefly;

/**
 * Base trait to deploy tasks file.
 * @category Trait
 * @package glowieframework/deploy
 * @author Glowie
 * @copyright Copyright (c) Glowie
 * @license MIT
 * @link https://glowie.gabrielsilva.dev.br
 */
trait Tasks
{

    /**
     * Array of commands to be run on each server.
     * @var array
     */
    private $__scripts = [];

    /**
     * Runs a command in a server.
     * @param string $command Command to run.
     * @param mixed $server (Optional) Server name (or an array of server names) where to run the command. Leave empty for all.
     */
    public function command(string $command, $server = null)
    {
        // If server is not defined, get all names
        if (empty($server)) $server = array_keys(config('deploy.servers', []));

        // Add the command to the server history
        foreach ((array)$server as $serverName) {
            if (!isset($this->__scripts[$serverName])) $this->__scripts[$serverName] = [];
            $this->__scripts[$serverName][] = $command;
        }
    }

    /**
     * Processes the commands at once.
     */
    public function processCommands()
    {
        foreach ($this->__scripts as $serverName => $scripts) {
            $this->runScriptsOnServer($scripts, $serverName);
        }

        // Clear the scripts
        $this->__scripts = [];
    }

    /**
     * Run a set of scripts in a remote server.
     * @param array $scripts Array of scripts.
     * @param string $serverName Name of the server.
     */
    private function runScriptsOnServer(array $scripts, string $serverName)
    {
        // Checks if the server config exists
        $serverInfo = config("deploy.servers.$serverName");
        if (empty($serverInfo)) throw new Exception("Server \"$serverName\" configuration does not exist");

        // Parses the scripts to a single command
        $command = implode(' && ', $scripts);

        // Checks if the connection exists
        $connection = Connections::get($serverName);
        if (!$connection) $connection = Connections::connect($serverName, $serverInfo);

        // Runs the command in the connection
        if ($connection) {
            $this->print("[$serverName] => $command", 'magenta');

            $error = null;
            $output = $connection->exec($command, $error);

            if ($error) {
                foreach (explode(PHP_EOL, $error) as $line) {
                    $line = trim($line);
                    if ($line !== '') $this->print("    >> $line", 'red');
                }

                throw new Exception("Command \"$command\" failed on server \"$serverName\"");
            } else if (!empty($output)) {
                foreach (explode(PHP_EOL, $output) as $line) {
                    $line = trim($line);
                    if ($line !== '') $this->print("    >> $line", 'yellow');
                }
            }
        }
    }

    /**
     * Gets an argument value.
     * @param string $arg Argument key to get.
     * @param mixed $default (Optional) Default value to return if the key does not exist.
     * @return mixed Returns the value if exists or the default if not.
     */
    public function getArg(string $arg, $default = null)
    {
        return Firefly::getArg($arg, $default);
    }

    /**
     * Prints a message in the console.
     * @param string $message Message to print.
     * @param string|null $color (Optional) Message color (check Firefly CLI available colors).
     */
    public function print(string $message, ?string $color = null)
    {
        if (!$color) return Firefly::print($message);
        Firefly::print("<color=\"$color\">$message</color>");
    }

    /**
     * Sends a notification to Telegram.
     * @param string $message Message to send.
     * @return bool Returns true on success, false otherwise.
     */
    public function notifyTelegram(string $message)
    {
        return Notify::telegram($message);
    }

    /**
     * Sends a notification to Discord.
     * @param string $message Message to send.
     * @return bool Returns true on success, false otherwise.
     */
    public function notifyDiscord(string $message)
    {
        return Notify::discord($message);
    }

    /**
     * Sends a push notification with Alertzy.
     * @param string $message Message to send.
     * @return bool Returns true on success, false otherwise.
     */
    public function notifyAlertzy(string $message)
    {
        return Notify::alertzy($message);
    }

    /**
     * Sends a notification to Slack.
     * @param string $message Message to send.
     * @return bool Returns true on success, false otherwise.
     */
    public function notifySlack(string $message)
    {
        return Notify::slack($message);
    }
}
