> This library was strongly inspired by [Symfony Console](https://symfony.com/doc/current/components/console.html)

<div id="autoloader-logo" align="center">
    <h1 style="font-weight:bold">Console Lite - BiuradPHP Toolbox</h1>
    <br />
    <img src="https://raw.githubusercontent.com/biurad/Console-lite/master/logo.png" alt="Autoloader Jet Logo" height="200px" width="400px"/>
    <h3>This is the light weight version of Symfony Console. @author Divine Niiquaye.</h3>
</div>

<div id="badges" align="center">

[![Latest Stable Version](https://poser.pugx.org/biurad/consolelite/v/stable)](https://packagist.org/packages/biurad/consolelite)
[![Build Status](https://travis-ci.org/biurad/Console-lite.svg?branch=master)](https://travis-ci.org/biurad/Console-lite)
[![Total Downloads](https://poser.pugx.org/biurad/consolelite/downloads)](https://packagist.org/packages/biurad/consolelite)
![GitHub issues](https://img.shields.io/github/issues/biurad/console-lite.svg)
[![StyleCI](https://github.styleci.io/repos/186709012/shield?branch=master)](https://github.styleci.io/repos/186709012)
[![BCH compliance](https://bettercodehub.com/edge/badge/biurad/Autoloader?branch=master)](https://bettercodehub.com/)
[![Codacy Badge](https://api.codacy.com/project/badge/Grade/e08ae4d55074443f8dd4fd96042c36e0)](https://app.codacy.com/app/biustudio/Console-lite?utm_source=github.com&utm_medium=referral&utm_content=biurad/Console-lite&utm_campaign=Badge_Grade_Dashboard)
[![License](https://poser.pugx.org/biurad/consolelite/license)](https://packagist.org/packages/biurad/consolelite)

</div>

The Console tool allows you to create command-line commands. Your console commands can be used for any recurring task, such as cronjobs, imports, or other batch jobs.

# Installation

Just run this composer command:

```bash
composer require biurad/consolelite
```

# Quickstart

## Creating a Console Application

First, you need to create a PHP script to define the console application:

```php
#!/usr/bin/env php
<?php
// application.php
use BiuradPHP\Toolbox\ConsoleLite\Application;

require __DIR__.'/vendor/autoload.php';

$application = new Application();

// ... register commands

$application->run();
```

Console Lite has a totally different approach in building console commands, not similar to Symfony Console but similar to Laravel Artisan. This was done in order to make it light weight.

You can register the commands using two different ways:

1. ```php
   // ...
   $application->register(new GenerateCommand());
    ```

2. ```php
   // ...
   $application->command('hello', 'Enter your name to start', function () {
        $this->writeln('Hello World');
   });

## Console Command Example

### This example is to print out a name without creating a class file

```php
#!/usr/bin/env php
<?php

use BiuradPHP\Toolbox\ConsoleLite\Application;

require __DIR__.'/vendor/autoload.php';

// 1. Initialize app
$application = new Application;

// 2. Register commands
$application->command('hello {name}', 'Enter your name', function($name) {
    $this->writeln("Hello {$name}");
});

// 3. Run app
$application->run();
```

### This example is to print out a name creating a class file

```php
<?php

use BiuradPHP\Toolbox\ConsoleLite\Command;

// create a NameCommand.php
class NameCommand extends Command
{
    /** @param string   The Application Name */
    protected $app          =   'Name Command';

    /** @param string   Your Set Command */
    protected $signature    =   'hello {name}';

    /** @param string   Your Command Description */
    protected $description  =   'Enter your name';

    /**
    * The Command Handler
    *
    * @param string $name   The Name input value.
    *
    * @return void
    */
    public function handle($name)
    {
        return $this->writeln("Hello {$name}");
    }
}
```

After that you create a filename without any path or file extention:

example, create a **console** `file` without file extention.

```php
#!/usr/bin/env php
<?php

use BiuradPHP\Toolbox\ConsoleLite\Application;
use NameCommand;

require __DIR__.'/vendor/autoload.php';

// 1. Initialize app
$application = new Application;

// 2. Register commands
$application->register(new NameCommand);

// 3. Run app
$application->run();
```

### Show Help

You can show help by putting `--help` or `-h` for each command. For example:

```bash
php console hello --help
```

### Enable Color

You can enable color by putting `--color` or `-c` for each command. For example:

```bash
php console hello --color
```

### Disable Color

You can disable color by putting `--no-color` or `-n` for each command. For example:

```bash
php console hello --no-color
```

# Command Usage and Options

The command has a powerful property called signature, this contains all the commands, options and arguements.

## The basic usage is simple

- Create a `class` and extend it to `BiuradPHP\Toolbox\ConsoleLite\Command`.

- Or use the method `command` from `BiuradPHP\Toolbox\ConsoleLite\Application`.

- Implement the `properties` method and register options, arguments, commands and set help texts

  - Implementing decription to command, add the following to the protected property $signature or method `command` of Application class.
  
    ```php
    protected $description = 'Enter a description'; // add a general description.
      ```

    ```php
    <?php
    // application.php
    use BiuradPHP\Toolbox\ConsoleLite\Application;

    require __DIR__.'/vendor/autoload.php';

    $app = Application;
    $app->command('hello', 'This is a description'/** add a general description to the second parameter. */, function () {
        $this->writeln('Hello World');
    });
      ```

  - Implementing add command, the protected proterty $signature holds the command, same applies to Application method `command`.
  
    ```php
    protected $signature = 'hello'; // add a command.
      ```

  - Implementing options, add the following to the protected property $signature.
  
      ```php
    protected $signature = 'hello {--option} {--anotheroption}'; // the '--' represents an option.
      ```

  - Implementing options has an input, add the following to the protected property $signature.
  
      ```php
    protected $signature = 'hello {--option=} {--anotheroption=}'; // the '=' represents an option has an input.
      ```

  - Implementing arguements, add the following to the protected property $signature.
  
      ```php
    protected $signature = 'hello {arguement} {anotherarguement}'; // this represents an argument.
      ```

  - Implementing description for options and arguements, add the following to the protected property $signature.
  
      ```php
    protected $signature = 'hello {arguement::Description} {--option::Description} {--option=::Description}'; // the '::' represents a description.
      ```

- > NB: This applies to `command` method in Application class.

- Implement the `handle` method and do your business logic there.

  - Open the file Command.php in folder `src` and find out the methods to use from there.

## Exceptions

By default the CLI classes registers two error or exception handlers.

- Application Exception
   To use the Application Exception, use example:

   ```php
    #!/usr/bin/env php
    <?php

    use BiuradPHP\Toolbox\ConsoleLite\Application;
    use BiuradPHP\Toolbox\ConsoleLite\Exception\JetErrorException;

    require __DIR__.'/vendor/autoload.php';

    $application = new Application;

    $application->command('exception {--test} {--error} {--replace}', 'This is an exception test', function () {
        // This throws an application exception.
        if ($this->hasOption('test')) {
            throw new JetErrorException('Test option not allowed');
        }

        // This throws a deprecated exception.
        if ($this->hasOption('error')) {
            throw new JetErrorException::deprecated('--error', '--replace', 'option');
        }

        $this->block('This is an Exception test');
    });

    $application->run();
   ```

## Colored output

Colored output is handled through the `Colors` class. It tries to detect if a color terminal is available and only
then uses terminal colors. You can always suppress colored output by passing ``--no-color`` to your scripts.

Simple colored messages can be printed by you using the convinence methods `writeln()`, `write()`, `color()`, `style()`,
`error()` (red), `success()` (red) or `block()`. Each of this methods contains three parameters, one for message, two for color,
and three for background color.

The formatter allows coloring full columns. To use that mechanism pass an array of colors as third parameter to
its `format()` method. Please note that you can not pass colored texts in the second parameters (text length calculation
and wrapping will fail, breaking your texts).

## Formatter

The `Formatter` class allows you to align texts in multiple columns. It tries to figure out the available
terminal width on its own. It can be overwritten by setting a `COLUMNS` environment variable.

The formatter is used through the `format()` method which expects at least two arrays: The first defines the column
widths, the second contains the texts to fill into the columns. Between each column a border is printed (a single space
by default).

The formatter contains other useful methods used to format time, memory, file paths and more.

Columns width can be given in three forms:

- fixed width in characters by providing an integer (eg. ``15``)
- precentages by provifing an integer and a percent sign (eg. ``25%``)
- a single fluid "rest" column marked with an asterisk (eg. ``*``)

When mixing fixed and percentage widths, percentages refer to the remaining space after all fixed columns have been
assigned.

Space for borders is automatically calculated. It is recommended to always have some relative (percentage) or a fluid
column to adjust for different terminal widths.

The formatter is used for the automatic help screen accessible when calling your script with ``-h`` or ``--help``.

## PSR-3 Logging

The CLI class is a fully PSR-3 compatible logger (printing colored log data to STDOUT and STDERR). This is useful when
you call backend code from your CLI that expects a Logger instance to produce any sensible status output while running.

To use this ability simply inherit from `BiuradPHP\Toolbox\ConsoleLite\PSR3` instead of `BiuradPHP\Toolbox\ConsoleLite\Command`, then pass `$this`
as the logger instance. Be sure you have the suggested `psr/log` composer package installed.

# License

- [MIT](LICENSE)
- [Divine Niiquaye](https://instagram.com/legendborn_gh)
