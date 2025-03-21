<?php

use Glowie\Plugins\Deploy\Core\Tasks;
use Env;
use Throwable;

return new class {
    use Tasks;

    /**
     * Servers info.
     * @var array
     */
    private $servers = [];

    /**
     * Here you can setup the deploy and configure your servers.
     */
    public function init()
    {
        $this->servers['localhost'] = [
            'host' => Env::get('SSH_HOST'),
            'port' => Env::get('SSH_PORT', 22),
            'auth' => Env::get('SSH_AUTH', 'password'),
            'username' => Env::get('SSH_USER'),
            'password' => Env::get('SSH_PASSWORD')
        ];
    }

    /**
     * Deploy task.
     */
    public function deploy()
    {
        //
    }

    /**
     * This method runs when the task finishes successfully.
     * @param string $task Receives the task name.
     */
    public function success(string $task)
    {
        //
    }

    /**
     * This method runs when the task fails.
     * @param string $task Receives the task name.
     * @param Throwable $th Receives the exception.
     */
    public function fail(string $task, Throwable $th)
    {
        //
    }
};
