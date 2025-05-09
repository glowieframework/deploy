<?php

use Glowie\Plugins\Deploy\Core\CLI;

require 'phar://' . Phar::running(false) . '/vendor/autoload.php';

CLI::run();
