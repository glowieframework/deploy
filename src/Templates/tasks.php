<?php

use Glowie\Plugins\Deploy\Core\Tasks;

/*
    ---------------------------
    Deploy tasks file
    ---------------------------
    Your deploy tasks must be configured here.
    Check Deploy docs for more information.
*/

return new class {
    use Tasks;

    /**
     * This method runs before every task.
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
     * This method runs when any task finishes successfully.
     * @param string $task Receives the task name.
     */
    public function success(string $task)
    {
        //
    }

    /**
     * This method runs when any task fails.
     * @param string $task Receives the task name.
     * @param Throwable $th Receives the exception.
     */
    public function fail(string $task, Throwable $th)
    {
        //
    }
};
