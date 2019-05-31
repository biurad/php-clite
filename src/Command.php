<?php

/**
 * The Biurad Library Autoload via cli.
 *
 * This is an extensible library which has all the commands methods to load classes
 * from namespaces and files just like AutoLoadOne.
 * But this is built with little procedural php.
 *
 * @see ReadMe.md to know more about how to load your
 * classes via command line.
 *
 * @author Divine Niiquaye <hello@biuhub.net>
 * @author Muhammad Syifa <emsifa@gmail.com>
 */

namespace BiuradPHP\Toolbox\ConsoleLite;

/**
 * The Command class contains the core functionality of the framework.
 * It is responsible for loading an methods from the application class,
 * running the registered commands, and generating response.
 *
 * @method void block(mixed $message, string $fgColor, string $bgColor, int $width)
 * This draws a line around a text.
 * @method void helpblock(mixed $name, mixed $description, mixed $namewidth, mixed $deswidth)
 * This has a seperator between message and description.
 *
 * use percentage or '*' to define custom width.
 * @method void color(mixed $message,  string $fgcolor,string  $bgcolor)
 * Use this inbetween $this->write(), $this->writeln, $this->block, $this->helpblock.
 * @method void style(mixed $text, string $fgColor, string $bgColor)
 * Alternative for $this->color.
 * @method void write(mixed $message, string $fgcolor, string  $bgcolor)
 * Write a text into cammandline interface.
 * @method void writeln(mixed $message,  string $fgcolor,string  $bgcolor)
 * Alternatively to $this->write(), a line break is ended with the output.
 * @method void success(mixed $message, int $width)
 * This outputs a text in success format on commandline interface.
 *
 * @see ReadMe.md file that came with the project.
 *
 * @method void defineTitle(mixed $name, string $output,string $color, string $bgcolor)
 * This Defines and prints the title in commandline interface.
 *
 * $output = 'writeln' or $output = 'write' or $output = 'block'.
 * @method mixed getTitle()
 * This get's the set title.
 * @method \BiuradPHP\Toolbox\ConsoleLite\Application execute(string $command)
 * Exexcutes a command, do not use shell_exex, exec, or passthru
 * @method array getCommandsLike(string $keyword)
 * Gets command like keyword.
 * @method bool isWindows()
 * Checks if it's Windows OS.
 * @method bool isLinux()
 * Checks if it's Linux OS.
 * @method array getRegisteredCommands()
 * Get's all avialiable commands registered within the Application.
 * @method string getFilename()
 * Get's the filename where the application is running from.
 * @method mixed hasCommand(string $signature)
 * Checks where a command has been set or registered.
 * @method mixed hasOption(string $options)
 * Checks where an option has be set or registered.
 * @method array getOptions()
 * Gets all avialiable options.
 * @method array getArguements()
 * Gets all avialable arguements.
 * @method bool hasArgument(int $name)
 * Returns true if an Argument object exists by name or position.
 * @method bool hasParameterOption(mixed $values, bool $onlyParams = false)
 * Check whether an option has a parameter.
 * @method mixed getParameterOption(mixed $values, bool $default = false, bool $onlyParams = false)
 * Get the parameter for an option.
 * @method mixed getCommand(mixed $name)
 * @method void line(int $num = 1, bool $line = false)
 * Enter a number of empty lines.
 * @method string shortAlias(string $key)
 * Enter a short alias for an option.
 * @method mixed|null ask(mixed $question, string $default)
 * Ask a question.
 * @method string prompt()
 * Prompts the user for input and shows what they type.
 * @method string hiddenPrompt(bool $allowFallback = false)
 * Prompts the user for input and hides what they type.
 * @method bool confirm(string $question, bool $default = false)
 * Confirm an input.
 * @method bool choice(mixed $question, string $default, array $choices, string $errorMessage)
 * Allows only two choices for now. just like true or false.
 *
 * @author Divine Niiquaye <hello@biuhub.net>
 */
abstract class Command
{
    protected $app;

    protected $signature;

    protected $description;

    public function getSignature()
    {
        return $this->signature;
    }

    public function getDescription()
    {
        return $this->description;
    }

    public function defineApp(Application $app)
    {
        if (!$this->app) {
            $this->app = $app;
        }
    }

    public function getApp()
    {
        $this->app;
    }

    public function __call($method, $args)
    {
        if ($this->app and method_exists($this->app, $method)) {
            return call_user_func_array([$this->app, $method], $args);
        } else {
            $class = get_class($this);

            throw new \BadMethodCallException(sprintf('Call to undefined method %s::%s', $class, $method));
        }
    }
}
