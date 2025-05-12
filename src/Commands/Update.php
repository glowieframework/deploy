<?php

namespace Glowie\Plugins\Deploy\Commands;

use Glowie\Core\CLI\Command;
use Glowie\Core\Tools\Crawler;
use Phar;

/**
 * Command to update the standalone binary.
 * @category Command
 * @package glowieframework/deploy
 * @author Glowie
 * @copyright Copyright (c) Glowie
 * @license MIT
 * @link https://glowie.gabrielsilva.dev.br
 */
class Update extends Command
{

    /**
     * Repository URL.
     * @var string
     */
    private const GIT_URL = 'https://raw.githubusercontent.com/glowieframework/deploy/refs/heads/main/';

    /**
     * The command handler.
     */
    public function run()
    {
        $curVersion = self::getCurrentVersion();
        $lastVersion = self::getLastVersion();
        if (empty($curVersion) || empty($lastVersion)) return $this->unableToUpdate();
        if ($curVersion !== $lastVersion) return $this->performUpdate();
        $this->success('[Deploy] Your version is up to date.');
    }

    /**
     * Performs the binary update.
     */
    private function performUpdate()
    {
        // Checks if the bin location is writable
        $path = Phar::running(false);
        if (!is_writable($path)) return $this->unableToUpdate();

        // Gets the latest binary
        $data = (new Crawler())->bypassVerification()->get(self::GIT_URL . 'bin/deploy');
        if (empty($data->body)) return $this->unableToUpdate();

        // Updates the file
        file_put_contents($path, $data->body);
        $this->success('[Deploy] Updated successfully.');
    }

    /**
     * Sends an unable to update error.
     */
    private function unableToUpdate()
    {
        $this->fail('[Deploy] Unable to update. Please download the last version manually from our website.');
        exit(127);
    }

    /**
     * Gets the current binary version.
     * @return string|false The current version on success, false on error.
     */
    public static function getCurrentVersion()
    {
        $file = __DIR__ . '/../../version.txt';
        if (!is_file($file)) return false;
        return trim(file_get_contents($file));
    }

    /**
     * Gets the last version from the repository.
     * @return string|false The last version on success, false on error.
     */
    public static function getLastVersion()
    {
        $lastVersion = (new Crawler())->bypassVerification()->get(self::GIT_URL . 'version.txt');
        if (empty($lastVersion->body)) return false;
        return trim($lastVersion->body);
    }
}
