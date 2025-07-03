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
     * This method runs before the story.
     * @param string $story Receives the story name.
     */
    public function initStory(string $story)
    {
        //
    }

    /**
     * The deploy story itself. It must call tasks via the `task()` method.
     */
    public function pipeline()
    {
        $this->task('deploy');
    }

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
    public function done(string $task)
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

    /**
     * This method runs when the story finishes successfully.
     * @param string $story Receives the story name.
     */
    public function doneStory(string $story)
    {
        //
    }

    /**
     * This method runs when the story fails.
     * @param string $story Receive sthe story name.
     * @param Throwable $th Receives the exception.
     */
    public function failStory(string $story, Throwable $th)
    {
        //
    }
};
