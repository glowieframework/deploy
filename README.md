# Glowie Deploy

This is a plugin for [Glowie Framework](https://github.com/glowieframework/glowie) to deploy applications using automated SSH scripts. It allows support for notifications, tasks and environment variables.

## Requirements

**This plugin requires the `ssh2` PHP extension.**

## Installation

Install in your Glowie project using Composer:

```shell
composer require glowieframework/deploy
```

Then add the Deploy class to the `app/config/Config.php` file, into the `plugins` array:

```php
'plugins' => [
    // ... other plugins here
    \Glowie\Plugins\Deploy\Deploy::class,
],
```

Make sure to publish the plugin files with the CLI:

```shell
php firefly publish
```

This will create a `Deploy.php` file in your `app/config` folder, this is where the plugin settings will be stored.

It will also create a `.deploy-tasks.php` file in the root of your application, this is the main entry point for your deploy scripts.

## Writing tasks

Your deploy tasks must be written in the `.deploy-tasks.php` file. A task is a PHP function that will be called in the deploy lifecycle.

To run shell commands in the target server, use:

```php
public function deploy(){

    $this->command('cd /var/www/my-project');

    $this->command('git pull');

    $this->command('php firefly migrate');
}
```

Each command will run in order and wait for the previous command to finish before its execution. If a remote command fails or return an exit code greater than `0`, it will throw an exception and end the script.

Each command output will be printed to the terminal upon its execution.

## Running a deploy task

Open the terminal in the root of your application and run:

```shell
php firefly deploy:run
```

This will run the default `deploy()` task. If you want to run another task, pass the task name as:

```shell
php firefly deploy:run --task=myTask
```
