<?php

namespace Glowie\Plugins\Deploy\Core;

use Config;
use Env;
use Util;
use Glowie\Core\CLI\Firefly;
use Glowie\Core\Error\HandlerCLI;
use Glowie\Core\Exception\FileException;
use Glowie\Plugins\Deploy\Deploy;

/**
 * Standalone CLI tool.
 * @category Plugin
 * @package glowieframework/deploy
 * @author Glowie
 * @copyright Copyright (c) Glowie
 * @license MIT
 * @link https://glowie.gabrielsilva.dev.br
 */
class CLI
{
    /**
     * Command arguments.
     * @var array
     */
    private static $args = [];

    /**
     * Indicates if the standalone mode is running.
     * @var bool
     */
    private static $isRunning = false;

    /**
     * Runs the CLI tool.
     */
    public static function run()
    {
        // Register settings
        global $argv;
        self::$args = $argv;
        self::$isRunning = true;

        // Store application start time
        define('APP_START_TIME', microtime(true));

        // Store application folder and location
        define('APP_FOLDER', '');
        define('APP_LOCATION', getcwd() . '/app/');

        // Load environment configuration
        Env::load();

        // Gets the command
        array_shift(self::$args);
        if (!isset(self::$args[0])) {
            Firefly::print(Firefly::color('Glowie Deploy | Standalone mode', 'magenta'));
            Firefly::print(Firefly::color('Usage: deploy run [options]', 'yellow'));
            return;
        }

        // Parses the command and args
        $command = trim(self::$args[0]);
        self::parseArgs();

        // Loads the configuration file
        self::loadConfig();

        // Register error handling
        HandlerCLI::register();

        // Register default commands
        (new Deploy())->register();

        // Runs the command
        self::triggerCommand($command);
    }

    /**
     * Indicates if the standalone mode is running.
     * @return bool True if CLI mode, false otherwise.
     */
    public static function isRunning()
    {
        return self::$isRunning;
    }

    /**
     * Loads the configuration file from the project or sets the minimum default config.
     */
    private static function loadConfig()
    {
        // Sets the minimum configuration
        Config::set('error_reporting.level', E_ALL);
        Config::set('error_reporting.logging', false);
        Config::set('deploy.servers.localhost.local', true);

        // Checks if a config file path was passed
        if (!empty(self::$args['config'])) {
            $file = self::$args['config'];
            if (!is_file($file)) throw new FileException('Config file "' . $file . '" was not found');

            $config = require_once($file);
            foreach ($config as $key => $value) {
                Config::set($key, $value);
            }

            return;
        }

        // Checks if the project has a config file and loads it
        $file = Util::location('config/Config.php');
        if (is_file($file)) Config::load();
    }

    /**
     * Parses the CLI arguments.
     */
    private static function parseArgs()
    {
        // Removes the command from the args
        array_shift(self::$args);

        // Parses the arguments as an associative array
        $args = [];
        foreach (self::$args as $value) {
            $match = [];

            // Args with values
            if (preg_match('/^--([^=]+)=(.+)$/', $value, $match)) {
                $args[strtolower($match[1])] = $match[2];
            } else if (preg_match('/^--([a-zA-Z0-9_-]+)$/', $value, $match)) {
                // Args without values
                $args[strtolower($match[1])] = '';
            }
        }

        // Returns the result
        self::$args = $args;
    }

    /**
     * Triggers a Deploy command.
     * @param string $command Command to trigger.
     */
    private static function triggerCommand(string $command)
    {
        $command = Util::kebabCase($command);
        Firefly::call("deploy:$command", self::$args);
    }
}
