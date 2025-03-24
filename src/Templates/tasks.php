<?php

use Glowie\Plugins\Deploy\Core\Tasks;

return new class {
    use Tasks;

    /**
     * This method runs before the task.
     * @param string $task Receives the task name.
     */
    public function init(string $task)
    {
        //
    }

    /**
     * The deploy task itself. All the commands must run here.
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
