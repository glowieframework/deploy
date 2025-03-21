<?php

namespace Glowie\Plugins\Deploy\Core;

use Exception;
use Glowie\Core\CLI\Firefly;

trait Tasks
{

    /**
     * Runs a set of commands in a server.
     * @param array $commands List of commands to run.
     * @param mixed $server (Optional) Server name (or an array of server names) where to run the command. Leave empty for all.
     */
    public function commands(array $commands = [], $server = null)
    {
        $this->command(implode(' && ', $commands), $server);
    }

    /**
     * Runs a command in a server.
     * @param string $command Command to run.
     * @param mixed $server (Optional) Server name (or an array of server names) where to run the command. Leave empty for all.
     */
    public function command(string $command, $server = null)
    {
        foreach ($this->servers as $serverName => $serverInfo) {
            // Checks if this command should run on the server
            if (!empty($server) && !in_array($serverName, (array)$server)) {
                continue;
            }

            // Checks if the connection exists
            $connection = Connections::get($serverName);
            if (!$connection) $connection = Connections::connect($serverName, $serverInfo);

            // Runs the command in the connection
            if ($connection) {
                $this->print("[$serverName] => $command", 'magenta');

                $error = null;
                $output = $connection->exec($command, $error);

                if ($error) {
                    $error = explode(PHP_EOL, $error);

                    foreach ($error as $line) {
                        $line = trim($line);
                        if ($line !== '') $this->print("    >> $line");
                    }

                    throw new Exception("Command \"$command\" failed on server \"$serverName\"");
                } else if (!empty($output)) {
                    $output = explode(PHP_EOL, $output);

                    foreach ($output as $line) {
                        $line = trim($line);
                        if ($line !== '') $this->print("    >> $line");
                    }
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
     * @param string $botId Bot token ID.
     * @param string $chatId Target chat ID.
     * @param string $message Message to send.
     * @return bool Returns true on success, false otherwise.
     */
    public function notifyTelegram(string $botId, string $chatId, string $message)
    {
        return Notify::telegram($botId, $chatId, $message);
    }

    /**
     * Sends a notification to Discord.
     * @param string $webhookUrl Webhook URL.
     * @param string $message Message to send.
     * @return bool Returns true on success, false otherwise.
     */
    public function notifyDiscord(string $webhookUrl, string $message)
    {
        return Notify::discord($webhookUrl, $message);
    }
}
