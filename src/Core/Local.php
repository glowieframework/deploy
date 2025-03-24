<?php

namespace Glowie\Plugins\Deploy\Core;

/**
 * Deploy local commands handler.
 * @category Plugin
 * @package glowieframework/deploy
 * @author Glowie
 * @copyright Copyright (c) Glowie
 * @license MIT
 * @link https://glowie.gabrielsilva.dev.br
 */
class Local
{

    /**
     * Executes a command locally.
     * @param string $command Command to run.
     * @param string|null &$stdErr Variable to store the errors, if any.
     * @return mixed Returns the command output.
     */
    public function exec(string $command, &$stdErr = null)
    {
        $output = [];
        $code = 0;

        exec($command . ' 2>&1', $output, $code);

        if ($code !== 0) {
            $stdErr = implode(PHP_EOL, $output);
            return null;
        }

        $stdErr = null;
        return implode(PHP_EOL, $output);
    }

    /**
     * Fake a disconnect method for compatibility.
     */
    public function disconnect()
    {
        return true;
    }
}
