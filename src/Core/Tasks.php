<?php

namespace Glowie\Plugins\Deploy\Core;

use Exception;
use Glowie\Core\CLI\Firefly;

trait Tasks
{

    public function commands(array $commands = [], $server = null)
    {
        return $this->command(implode(' && ', $commands), $server);
    }

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
                        if (!empty($line)) $this->print("    >> $line", 'red');
                    }

                    throw new Exception("Command \"$command\" failed on server \"$serverName\"");
                } else if (!empty($output)) {
                    $output = explode(PHP_EOL, $output);

                    foreach ($output as $line) {
                        $line = trim($line);
                        if (!empty($line)) $this->print("    >> $line", 'yellow');
                    }
                }
            }
        }
    }

    public function disconnectAll()
    {
        foreach ($this->__connections as $name => $connection) {
            $connection->disconnect();
            unset($this->__connections[$name]);
        }
    }

    public function print(string $message, ?string $color = null)
    {
        if (!$color) return Firefly::print($message);
        Firefly::print("<color=\"$color\">$message</color>");
    }
}
