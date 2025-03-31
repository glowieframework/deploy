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

### Specifying the target server

By default, all commands will run in all servers from the config file. If you want to run a command in a single server, pass the server name as the second argument of the `command()` method. You can also use an array of server names.

```php
// Runs only in "homologation" server
$this->command('cd /var/www/my-project', 'homologation');

// Runs in both "homologation" and "production" servers
$this->command('git pull', ['homologation', 'production']);
```

### Printing messages in the console

To print a custom message in the console, use:

```php
$this->print('My custom message');
```

You can also pass an optional color name as the second argument or use one of the aliases:

```php
$this->error('Something failed!');

$this->success('Everything works great.');

$this->warning('Be careful...');
```

### After success

If you want to do something when your task ends the execution with success (no command returned a code greater than `0`), create the following method in the tasks file:

```php
public function success(string $task){
    // You can do anything here
    $this->success("$task ran successfully!");
}
```

The method will receive the task name as the first parameter.

### Handling errors

If something in your task fails, the script execution will stop and a exception will be thrown. If you want to capture the error and do something with it (like sending a notification), create the following method in the tasks file:

```php
public function fail(string $task, Throwable $th){
    // You can do anything here
    $this->error("$task failed! Stack trace:");
    $this->error($th->getTraceAsString());
}
```

The method will receive the task name as the first parameter, and the exception as the second. This is called for errors in any task from the tasks file.

## Running a deploy task

Open the terminal in the root of your application and run:

```shell
php firefly deploy:run
```

This will run the default `deploy()` task. If you want to run another task, pass the task name as:

```shell
php firefly deploy:run --task=myTask
```

### Passing CLI arguments and options

If you want to pass a custom argument to the task, just send it in the terminal using the syntax:

```shell
php firefly deploy:run --version=1.0.0
```

To get this argument value from within your tasks file, use:

```php
$version = $this->getArg('version'); // returns "1.0.0"
```

You can also pass custom options:

```shell
php firefly deploy:run --production
```

And retrieve from your task:

```php
$isProduction = $this->hasOption('production'); // returns true
```

## Credits

Deploy and Glowie are currently being developed by [Gabriel Silva](https://gabrielsilva.dev.br).
