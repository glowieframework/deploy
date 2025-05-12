<?php

use Glowie\Plugins\Deploy\Core\CLI;

require Phar::running() . '/vendor/autoload.php';

CLI::run();
