<?php

namespace Glowie\Plugins\Deploy;

use Glowie\Core\CLI\Firefly;
use Glowie\Core\Plugin;
use Glowie\Plugins\Deploy\Commands\Run;

class Deploy extends Plugin
{

    /**
     * Initializes the plugin.
     */
    public function register()
    {
        Firefly::custom('deploy', Run::class);
    }
}
