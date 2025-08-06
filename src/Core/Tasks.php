<?php

namespace Glowie\Plugins\Deploy\Core;

use Config;
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
     * Array of exposed environment variables.
     * @var array
     */
    private $__env = [];

    /**
     * Current grouped server name.
     * @var mixed
     */
    private $__curServer = null;

    /**
     * Runs a command in a server.
     * @param string $command Command to run.
     * @param mixed $server (Optional) Server name (or an array of server names) where to run the command. Leave empty for all.
     * @return $this Current instance for nested calls.
     */
    final public function command(string $command, $server = null)
    {
        // If server is not defined, get all names
        if (empty($server) && !empty($this->__curServer)) {
            $server = $this->__curServer;
        } else if (empty($server)) {
            $server = array_keys(Config::get('deploy.servers', []));
        }

        // Add the command to the server history
        foreach ((array)$server as $serverName) {
            if (!isset($this->__scripts[$serverName])) $this->__scripts[$serverName] = [];
            $this->__scripts[$serverName][] = $command;
        }

        // Returns the current instance
        return $this;
    }

    /**
     * Runs a task.
     * @param string $task Task name.
     */
    final public function task(string $task)
    {
        // Checks if the task exists
        if (!is_callable([$this, $task])) throw new Exception("Task \"$task\" does not exist in the tasks file");

        try {
            // Calls the init for this task
            if (is_callable([$this, 'init'])) $this->init($task);

            // Calls the task
            $this->{$task}();
            $this->__processCommands();

            // Calls the done method for this task
            if (is_callable([$this, 'done'])) $this->done($task);
        } catch (\Throwable $th) {
            // On failure, calls the fail method if exists
            $this->clearScripts();
            if (is_callable([$this, 'fail'])) $this->fail($task, $th);
            throw new Exception("Task \"$task\" failed with message: " . $th->getMessage(), $th->getCode(), $th);
        }
    }

    /**
     * Runs a story.
     * @param string $story Story name.
     */
    final public function story(string $story)
    {
        // Checks if the story exists
        if (!is_callable([$this, $story])) throw new Exception("Story \"$story\" does not exist in the tasks file");

        try {
            // Calls the init for this story
            if (is_callable([$this, 'initStory'])) $this->initStory($story);

            // Calls the story
            $this->{$story}();

            // Calls the done method for this story
            if (is_callable([$this, 'doneStory'])) $this->doneStory($story);
        } catch (\Throwable $th) {
            // On failure, calls the fail method if exists
            $this->clearScripts();
            if (is_callable([$this, 'failStory'])) $this->failStory($story, $th);
            throw new Exception("Story \"$story\" failed with message: " . $th->getMessage(), $th->getCode(), $th);
        }
    }

    /**
     * Groups a set of commands into a server.
     * @param string|array $server (Optional) Server name (or an array of server names) where to run the command.
     * @param callable $callback Callback with grouped command calls.
     * @return $this Current instance for nested calls.
     */
    final public function on($server, callable $callback)
    {
        if (empty($server)) throw new Exception('Server name cannot be empty on grouped commands');
        $this->__curServer = $server;
        call_user_func_array($callback, [&$this]);
        $this->__curServer = null;
        return $this;
    }

    /**
     * Exposes an environment variable to the servers.
     * @param string $name Variable name.
     * @param mixed $value Variable value.
     * @return $this Current instance for nested calls.
     */
    final public function env(string $name, $value)
    {
        $this->__env[$name] = $value;
        return $this;
    }

    /**
     * Clears the server scripts.
     * @param string|null $serverName (Optional) Server name to clear scripts, leave empty to clear all.
     * @return $this Current instance for nested calls.
     */
    final public function clearScripts(?string $serverName = null)
    {
        if (is_null($serverName)) {
            $this->__scripts = [];
        } else {
            unset($this->__scripts[$serverName]);
        }
        return $this;
    }

    /**
     * Clears the environment variables.
     * @return $this Current instance for nested calls.
     */
    final public function clearEnv()
    {
        $this->__env = [];
        return $this;
    }

    /**
     * Processes the commands at once.
     */
    final public function __processCommands()
    {
        // Run the scripts for each server
        foreach ($this->__scripts as $serverName => $scripts) {
            $this->__runScriptsOnServer($scripts, $serverName);
        }

        // Clear the scripts and variables
        $this->clearScripts();
        $this->clearEnv();
    }

    /**
     * Run a set of scripts in a remote server.
     * @param array $scripts Array of scripts.
     * @param string $serverName Name of the server.
     */
    final public function __runScriptsOnServer(array $scripts, string $serverName)
    {
        // Checks if the server config exists
        $serverInfo = Config::get("deploy.servers.$serverName");
        if (empty($serverInfo)) throw new Exception("Server \"$serverName\" configuration does not exist");

        // Checks if the connection exists
        $connection = Connections::get($serverName);
        if (!$connection) $connection = Connections::connect($serverName, $serverInfo);

        // Runs the commands in the connection
        if ($connection) {
            // Sets the environment variables
            $connection->setEnv(array_merge($serverInfo['env'] ?? [], $this->__env));

            // Executes the commands
            $status = $connection->exec($scripts, function ($output) use ($serverName) {
                // Checks for empty outputs
                $output = trim($output);
                if ($output === '') return;

                // Prints the output line by line
                foreach (explode(PHP_EOL, $output) as $line) {
                    $this->print(Firefly::color("[$serverName]", 'magenta') . " $line");
                }
            });

            // Handle exit error code
            if ($status !== 0) throw new Exception("Command failed on server \"$serverName\" with exit code $status", $status);
        }
    }

    /**
     * Gets an argument value.
     * @param string $arg Argument key to get.
     * @param mixed $default (Optional) Default value to return if the key does not exist.
     * @return mixed Returns the value if exists or the default if not.
     */
    final public function getArg(string $arg, $default = null)
    {
        return Firefly::getArg($arg, $default);
    }

    /**
     * Checks if an option exists.
     * @param string $key Option key to get.
     * @return bool Returns true if the option exists, false otherwise.
     */
    final public function hasOption(string $key)
    {
        return $this->getArg($key) === '';
    }

    /**
     * Prints a message in the console.
     * @param string $message Message to print.
     * @param string $color (Optional) Message color (check Firefly CLI available colors).
     * @return $this Current instance for nested calls.
     */
    final public function print(string $message, string $color = 'default')
    {
        Firefly::print(Firefly::color($message, $color));
        return $this;
    }

    /**
     * Prints an error message in the console.
     * @param string $message Message to print.
     * @return $this Current instance for nested calls.
     */
    final public function error(string $message)
    {
        $this->print($message, 'red');
        return $this;
    }

    /**
     * Prints a warning message in the console.
     * @param string $message Message to print.
     * @return $this Current instance for nested calls.
     */
    final public function warning(string $message)
    {
        $this->print($message, 'yellow');
        return $this;
    }

    /**
     * Prints a success message in the console.
     * @param string $message Message to print.
     * @return $this Current instance for nested calls.
     */
    final public function success(string $message)
    {
        $this->print($message, 'green');
        return $this;
    }

    /**
     * Prints an info message in the console.
     * @param string $message Message to print.
     * @return $this Current instance for nested calls.
     */
    final public function info(string $message)
    {
        $this->print($message, 'cyan');
        return $this;
    }

    /**
     * Sends a notification to Telegram.
     * @param string $message Message to send.
     * @param array $options (Optional) Associative array of options to send in the request body.
     * @param string|null $botId (Optional) Custom bot ID to send the notification. Leave empty to use from your deploy config file.
     * @param string|null $chatId (Optional) Custom chat ID to send the notification. Leave empty to use from your deploy config file.
     * @return bool Returns true on success, false otherwise.
     */
    final public function notifyTelegram(string $message, array $options = [], ?string $botId = null, ?string $chatId = null)
    {
        return Notify::telegram($message, $options, $botId, $chatId);
    }

    /**
     * Sends a notification to Discord.
     * @param string $message Message to send.
     * @param array $options (Optional) Associative array of options to send in the request body.
     * @param string|null $webhookUrl (Optional) Custom webhook URL to send the notification. Leave empty to use from your deploy config file.
     * @return bool Returns true on success, false otherwise.
     */
    final public function notifyDiscord(string $message, array $options = [], ?string $webhookUrl = null)
    {
        return Notify::discord($message, $options, $webhookUrl);
    }

    /**
     * Sends a push notification with Alertzy.
     * @param string $message Message to send.
     * @param array $options (Optional) Associative array of options to send in the request body.
     * @param string|null $accountKey (Optional) Custom account key to send the notification. Leave empty to use from your deploy config file.
     * @return bool Returns true on success, false otherwise.
     */
    final public function notifyPush(string $message, array $options = [], ?string $accountKey = null)
    {
        return Notify::push($message, $options, $accountKey);
    }

    /**
     * Sends a notification to Slack.
     * @param string $message Message to send.
     * @param array $options (Optional) Associative array of options to send in the request body.
     * @param string|null $webhookUrl (Optional) Custom webhook URL to send the notification. Leave empty to use from your deploy config file.
     * @return bool Returns true on success, false otherwise.
     */
    final public function notifySlack(string $message, array $options = [], ?string $webhookUrl = null)
    {
        return Notify::slack($message, $options, $webhookUrl);
    }
}
