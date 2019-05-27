<?php

/**
 * The Biurad Library Autoload via cli
 * -----------------------------------------------.
 *
 * This is an extensible library used to load classes
 * from namespaces and files just like composer.
 * But this is built in procedural php.
 *
 * @see ReadMe.md to know more about how to load your
 * classes via command line.
 *
 * @author Divine Niiquaye <hello@biuhub.net>
 * @author Muhammad Syifa <emsifa@gmail.com>
 */

namespace BiuradPHP\Toolbox\ConsoleLite;

use Closure;
use Exception;
use InvalidArgumentException;
use BiuradPHP\Toolbox\ConsoleLite\Command\CommandList;
use BiuradPHP\Toolbox\ConsoleLite\Command\CommandAbout;
use BiuradPHP\Toolbox\ConsoleLite\Exception\JetErrorException;

class Application
{
    use Concerns\InputUtils;
    use Concerns\FileUtils;

    protected static $stty;
    protected static $shell;

    private $tokens;

    public $arguments = [];
    public $commands = [];
    public $options = [];
    public $optionsAlias = [];
    public $verbose = false;
    public $color;
    public $title = null;

    protected $filename;
    protected $command;
    protected $resolvedOptions = [];
    protected $formatter;
    protected $errorhandle;

    /** @var array PSR-3 compatible foreground color and their prefix, color, output channel */
    protected $foregroundColors = [
        'black' => ['', Colors::C_BLACK, STDOUT],
        'dark_gray' => ['', Colors::C_DARKGRAY, STDOUT],
        'blue' => ['', Colors::C_BLUE, STDOUT],
        'light_blue' => ['', Colors::C_LIGHTBLUE, STDOUT],
        'green' => ['', Colors::C_GREEN, STDOUT],
        'light_green' => ['', Colors::C_LIGHTGREEN, STDOUT],
        'cyan' => ['', Colors::C_CYAN, STDOUT],
        'light_cyan' => ['', Colors::C_LIGHTCYAN, STDOUT],
        'red' => ['', Colors::C_RED, STDERR],
        'light_red' => ['', Colors::C_LIGHTRED, STDERR],
        'purple' => ['', Colors::C_PURPLE, STDOUT],
        'magenta' => ['', Colors::C_MAGENTA, STDOUT],
        'light_purple' => ['', Colors::C_LIGHTPURPLE, STDOUT],
        'brown' => ['', Colors::C_BROWN, STDERR],
        'yellow' => ['', Colors::C_YELLOW, STDOUT],
        'light_gray' => ['', Colors::C_LIGHTGRAY, STDOUT],
        'white' => ['', Colors::C_WHITE, STDOUT],
    ];

    /**
     * Constructor.
     */
    public function __construct(string $title = 'Console Lite Application')
    {
        $argv = @$GLOBALS['argv'];
        $this->title = $this->line().$this->writeln($title).$this->line();

        // error handlers
        $this->errorhandle = new JetErrorException();
        set_exception_handler([$this, 'handleError']);
        error_reporting(0);

        $this->color = new Colors();
        $this->formatter = new Formatter($this->color);
        $this->tokens = $argv;

        list(
            $this->filename,
            $this->command,
            $this->arguments,
            $this->options,
            $this->optionsAlias
        ) = $this->parseArgv($argv);

        $this->loadCommands();
        $this->register(new CommandList());
        $this->register(new CommandAbout());
    }

    private function loadCommands()
    {
        $this->command('welcome {name?::Enter a name}', 'Enter your name to start', function ($name) {
            $this->block("Hello {$name}, Nice Meeting you, I'm Biurad Slim Lite Console.", 'white', 'black');
        });
    }

    /**
     * Gets the name of the application.
     *
     * @return string The application name
     */
    public function getName()
    {
        return $this->title;
    }

    /**
     * Sets the application name.
     *
     * @param string $name The application name
     */
    public function setName($name)
    {
        $this->title = $name;
    }

    /**
     * Type of instance
     *
     * @return bool
     */
    public function getType()
    {
        if (PHP_SAPI === 'cli') {
            php_sapi_name().' CLI';
        }
        if (PHP_SAPI === 'cgi') {
            php_sapi_name().' CGI';
        }
    }

    /**
     * Register command.
     *
     * @param Command $command
     */
    public function register(Command $command)
    {
        try {
            list($commandName, $args, $options) = $this->parseCommand($command->getSignature());

            if (!$commandName) {
                $class = get_class($command);

                throw new InvalidArgumentException(sprintf('Command %s must have a name defined in signature', $class));
            }

            if (!method_exists($command, 'handle')) {
                $class = get_class($command);

                throw new InvalidArgumentException(sprintf('Command %s must have a method handle', $class));
            }

            $command->defineApp($this);

            $this->commands[$commandName] = [
                'handler' => [$command, 'handle'],
                'description' => $command->getDescription(),
                'args' => $args,
                'options' => $options,
            ];
        } catch (JetErrorException $e) {
            $class = get_class($command);

            throw new JetErrorException(sprintf('%s could not be found', $class), $e->getCode());
        }
    }

    /**
     * Register closure command.
     *
     * @param string  $signature   command signature
     * @param string  $description command description
     * @param Closure $handler     command handler
     */
    public function command($signature, $description, Closure $handler)
    {
        list($commandName, $args, $options) = $this->parseCommand($signature);

        $this->commands[$commandName] = [
            'handler' => $handler,
            'description' => $description,
            'args' => $args,
            'options' => $options,
        ];
    }

    /**
     * Get registered commands.
     *
     * @return array
     */
    public function getRegisteredCommands()
    {
        return $this->commands;
    }

    /**
     * Get command by given key.
     *
     * @param string $command
     *
     * @return mixed
     */
    public function hasCommand($command)
    {
        return isset($this->commands[$command]) ? $this->commands[$command] : $this->command;
    }

    /**
     * Get commands like given keyword.
     *
     * @param string $keyword
     *
     * @return array
     */
    public function getCommandsLike($keyword)
    {
        $regex = preg_quote($keyword);
        $commands = $this->getRegisteredCommands();
        $matchedCommands = [];
        foreach ($commands as $name => $command) {
            if ((bool) preg_match('/'.$regex.'/', $name)) {
                $matchedCommands[$name] = $command;
            }
        }

        return $matchedCommands;
    }

    /**
     * Get filename.
     *
     * @return string
     */
    public function getFilename()
    {
        return $this->filename;
    }

    /**
     * Get options.
     *
     * @return array
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * Get arguments.
     *
     * @return array
     */
    public function getArguments()
    {
        return $this->arguments;
    }

    /**
     * Set argument.
     *
     * @return mixed
     */
    public function setArgument($key)
    {
        return array_key_exists($key, $this->arguments);
    }

    /**
     * Returns true if an Argument object exists by name or position.
     *
     * @param string|int $name The Argument name or position
     *
     * @return bool true if the Argument object exists, false otherwise
     */
    public function hasArgument($name)
    {
        $arguments = \is_int($name) ? array_values($this->arguments) : $this->arguments;

        return isset($arguments[$name]);
    }

    /**
     * Run app.
     */
    public function run()
    {
        return $this->execute($this->command);
    }

    /**
     * Execute command.
     *
     * @param string $command command name
     */
    public function execute($command)
    {
        if (!$command) {
            $command = 'list';
        }

        if (!isset($this->commands[$command])) {
            return $this->showCommandsLike($command);
        }

        if (array_key_exists('help', $this->options) || $this->shortAlias('h')) {
            return $this->showHelp($command);
        }

        if (array_key_exists('no-color', $this->options) || $this->shortAlias('n')) {
            $this->color->disable();
        }

        if (array_key_exists('color', $this->options)|| $this->shortAlias('c')) {
            $this->color->enable();
        }

        try {
            $handler = $this->commands[$command]['handler'];
            $arguments = $this->validateAndResolveArguments($command);
            $this->validateAndResolveOptions($command);

            if ($handler instanceof \Closure) {
                $handler = $handler->bindTo($this);
            }

            call_user_func_array($handler, $arguments);
        } catch (JetErrorException $e) {
            $this->handleError($e);
        }
    }

    /**
     * Get option by given key.
     *
     * @param string $key
     *
     * @return mixed
     */
    public function hasOption($key)
    {
        return isset($this->resolvedOptions[$key]) ? $this->resolvedOptions[$key] : null;
    }

    /**
     * Set option for command.
     *
     * @param string $key
     *
     * @return mixed
     */
    public function setOption($key)
    {
        return array_key_exists($key, $this->options);
    }

    /**
     * Has Option Alias.
     *
     * @param string $key
     *
     * @return mixed
     */
    public function shortAlias($key)
    {
        return array_key_exists($key, $this->optionsAlias);
    }

    /**
     * Get the value of the given option.
     *
     * Please note that all options are accessed by their long option names regardless of how they were
     * specified on commandline.
     *
     * Can only be used after parseOptions() has been run
     *
     * @param mixed       $option
     * @param bool|string $default what to return if the option was not set
     *
     * @return bool|string|string[]
     */
    public function getOption($option, $default = false)
    {
        if ($option === null or !$option or $option === '') {
            return;
        }

        if (isset($this->resolvedOptions[$option])) {
            return $this->resolvedOptions[$option];
        }

        return $default;
    }

    /**
     * Check whether an option has a parameter.
     *
     * @param mixed $values
     * @param bool  $onlyParams
     *
     * @return boolean
     */
    public function hasParameterOption($values, $onlyParams = false)
    {
        $values = (array) $values;

        foreach ($this->tokens as $token) {
            if ($onlyParams && '--' === $token) {
                return false;
            }
            foreach ($values as $value) {
                // Options with values:
                //   For long options, test for '--option=' at beginning
                //   For short options, test for '-o' at beginning
                $leading = 0 === strpos($value, '--') ? $value.'=' : $value;
                if ($token === $value || '' !== $leading && 0 === strpos($token, $leading)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Get the parameter for an option.
     *
     * @param mixed $values
     * @param bool  $default
     * @param bool  $onlyParams
     *
     * @return void
     */
    public function getParameterOption($values, $default = false, $onlyParams = false)
    {
        $values = (array) $values;
        $tokens = $this->tokens;

        while (0 < \count($tokens)) {
            $token = array_shift($tokens);
            if ($onlyParams && '--' === $token) {
                return $default;
            }

            foreach ($values as $value) {
                if ($token === $value) {
                    return array_shift($tokens);
                }
                // Options with values:
                //   For long options, test for '--option=' at beginning
                //   For short options, test for '-o' at beginning
                $leading = 0 === strpos($value, '--') ? $value.'=' : $value;
                if ('' !== $leading && 0 === strpos($token, $leading)) {
                    return substr($token, \strlen($leading));
                }
            }
        }

        return $default;
    }

    /**
     * Get the value of commands.
     *
     * @param mixed $name
     *
     * @return void
     */
    public function getCommand($name)
    {
        return $this->commands[$name];
    }

    /**
     * Write in a text.
     *
     * @param string $messages
     * @param string $fgColor
     * @param string $bgColor
     * @param array  $context
     *
     * @return void
     */
    public function write($messages, $fgColor = null, $bgColor = null, array $context = [])
    {
        if (!is_iterable($messages)) {
            $messages = [$this->interpolate($messages)];
        }

        foreach ($messages as $message) {
            if ($fgColor || $bgColor) {
                $message = $this->color($message, $fgColor, $bgColor, $context);
            }
            echo $this->interpolate($message, $context);
        }
    }

    /**
     * Write text line.
     *
     * @param string $message
     * @param string $fgColor
     * @param string $bgColor
     * @param array  $context
     *
     * @return void
     */
    public function writeln($message, $fgColor = null, $bgColor = null, array $context = [])
    {
        return $this->write($message, $fgColor, $bgColor, $context).$this->line();
    }

    /**
     * Enter a number of empty lines.
     *
     * @param int   $num     Number of lines to output
     * @param bool  $line    draws a formatter line
     * @param array $context
     *
     * @return void
     */
    public function line(int $num = 1, $line = false, array $context = [])
    {
        // Do it once or more, write with empty string gives us a new line
        for ($i = 0; $i < $num; ++$i) {
            if (false === $line) {
                $this->write(PHP_EOL, null, null, $context);
            } else {
                $this->write(str_pad('', $this->formatter->getMaxWidth(), '-')."\n", null, null, $context);
            }
        }
    }

    /**
     * Write error message.
     *
     * @param string $message
     * @param string $width
     * @param bool   $exit
     *
     * @return void
     */
    public function error($message, $width = null)
    {
        $this->block($message, 'white', 'red', $width);
    }

    /**
     * Write sucess message.
     *
     * @param string $message
     * @param string $width
     * @param bool   $exit
     *
     * @return void
     */
    public function success($message, $width = null)
    {
        $this->block($message, 'white', 'green', $width);
    }

    /**
     * Wraps a text with a line formatter given line breaks.
     *
     * @param string $message
     * @param string $fgColor
     * @param string $bgColor
     * @param string $width
     *
     * @return void
     */
    public function block($message, $fgColor = null, $bgColor = null, $width = null)
    {
        $this->formatter->getMaxWidth();
        if ($width !== null) {
            $this->formatter->setMaxWidth($width);
        }

        $this->line();
        $this->line(1, true);
        $this->formatter->wordwrap(
            $this->write($message, $fgColor, $bgColor)
        );
        $this->line();
        $this->line(1, true);
        $this->line();
    }

    /**
     * Write a help block.
     *
     * @param mixed  $name        is the subject
     * @param string $description is the description
     * @param mixed $nwid is the subject's width
     * @param mixed $dwid is the description width
     *
     * @return void
     */
    public function helpblock($name, $description, $nwid = '25%', $dwid = '*')
    {
        $this->formatter->setBorder(' -> '); // nice border between colmns
        $this->write(
            $this->formatter->format(
                [$nwid, $dwid], [$name, $description]
            )
        );
    }

    /**
     * Styles an output text.
     *
     * @param string $message
     * @param string $fgcolor
     * @param string $bgcolor
     * @param array  $context
     *
     * @return void
     */
    public function color($message, $fgcolor, $bgcolor = null, array $context = [])
    {
        // is this log fgcolor wanted?
        if (!isset($this->foregroundColors[$fgcolor])) {
            throw new InvalidArgumentException(sprintf('Invalid foreground color specified: "%s". Expected one of (%s)', $fgcolor, implode(', ', array_keys($this->foregroundColors))));
        }

        /** @var string $prefix */
        /** @var string $color */
        /** @var resource $channel */
        list($prefix, $color, $channel) = $this->foregroundColors[$fgcolor];
        if (!$this->color->isEnabled()) {
            $prefix = '';
        }

        $message = $this->interpolate($message, $context);
        $this->color->println($prefix.$message, strtolower($color), strtolower($bgcolor), $channel);
    }

    /**
     * Coloring text.
     *
     * @param string $text
     * @param string $fgColor
     * @param string $bgColor
     *
     * @return string
     */
    public function style($text, $fgColor, $bgColor = null)
    {
        return $this->color->wrap($text, strtolower($fgColor), strtolower($bgColor));
    }

    /**
     * Interpolates context values into the message placeholders.
     *
     * @param $message
     * @param array $context
     *
     * @return string
     */
    protected function interpolate($message, array $context = [])
    {
        // build a replacement array with braces around the context keys
        $replace = [];
        foreach ($context as $key => $val) {
            // check that the value can be casted to string
            if (!is_array($val) && (!is_object($val) || method_exists($val, '__toString'))) {
                $replace['{'.$key.'}'] = $val;
            }
        }

        // interpolate replacement values into the message and return
        return strtr($message, $replace);
    }

    /**
     * Parse Command Definition.
     *
     * @param array $command
     *
     * @return array
     */
    protected function parseCommand($command)
    {
        $exp = explode(' ', trim($command), 2);
        $command = trim($exp[0]);
        $args = [];
        $options = [];

        if (isset($exp[1])) {
            preg_match_all("/\{(?<name>\w+)(?<arr>\*)?((=(?<default>[^\}]+))|(?<optional>\?))?(::(?<desc>[^}]+))?\}/i", $exp[1], $matchArgs);
            preg_match_all("/\{--((?<alias>[a-zA-Z])\|)?(?<name>\w+)((?<valuable>=)(?<default>[^\}]+)?)?(::(?<desc>[^}]+))?\}/i", $exp[1], $matchOptions);
            foreach ($matchArgs['name'] as $i => $argName) {
                $default = $matchArgs['default'][$i];
                $expDefault = explode('::', $default, 2);
                if (count($expDefault) > 1) {
                    $default = $expDefault[0];
                    $description = $expDefault[1];
                } else {
                    $default = $expDefault[0];
                    $description = $matchArgs['desc'][$i];
                }

                $args[$argName] = [
                    'is_array' => !empty($matchArgs['arr'][$i]),
                    'is_optional' => !empty($matchArgs['optional'][$i]) || !empty($default),
                    'default' => $default ?: null,
                    'description' => $description,
                ];
            }

            foreach ($matchOptions['name'] as $i => $optName) {
                $default = $matchOptions['default'][$i];
                $expDefault = explode('::', $default, 2);
                if (count($expDefault) > 1) {
                    $default = $expDefault[0];
                    $description = $expDefault[1];
                } else {
                    $default = $expDefault[0];
                    $description = $matchOptions['desc'][$i];
                }
                $options[$optName] = [
                    'is_valuable' => !empty($matchOptions['valuable'][$i]),
                    'default' => $default ?: null,
                    'description' => $description,
                    'alias' => $matchOptions['alias'][$i] ?: null,
                ];
            }
        }

        return [$command, $args, $options];
    }

    /**
     * Parse PHP argv.
     *
     * @param array $argv
     *
     * @return array
     */
    protected function parseArgv(array $argv)
    {
        $filename = array_shift($argv);
        $command = array_shift($argv);
        $arguments = [];
        $options = [];
        $optionsAlias = [];

        while (count($argv)) {
            $arg = array_shift($argv);
            if ($this->isOption($arg)) {
                $optName = ltrim($arg, '-');
                if ($this->isOptionWithValue($arg)) {
                    list($optName, $optvalue) = explode('=', $optName);
                } else {
                    $optvalue = array_shift($argv);
                }

                $options[$optName] = $optvalue;
            } elseif ($this->isOptionAlias($arg)) {
                $alias = ltrim($arg, '-');
                $exp = explode('=', $alias);
                $aliases = str_split($exp[0]);
                if (count($aliases) > 1) {
                    foreach ($aliases as $aliasName) {
                        $optionsAlias[$aliasName] = null;
                    }
                } else {
                    $aliasName = $aliases[0];
                    if (count($exp) > 1) {
                        list($aliasName, $aliasValue) = $exp;
                    } else {
                        $aliasValue = array_shift($argv);
                    }

                    $optionsAlias[$aliasName] = $aliasValue;
                }
            } else {
                $arguments[] = $arg;
            }
        }

        return [$filename, $command, $arguments, $options, $optionsAlias];
    }

    /**
     * Check whether OS is windows.
     *
     * @return bool
     */
    public function isWindows()
    {
        if (defined('PHP_WINDOWS_VERSION_BUILD') || PHP_OS === 'WINNT') {
            return '\\' === DIRECTORY_SEPARATOR;
        }
    }

    /**
     * Checks whether OS is Linux.
     *
     * @return bool
     */
    public function isLinux()
    {
        if (PHP_OS === 'Linux') {
            return '/' === DIRECTORY_SEPERATOR;
        }
    }

    /**
     * Check whether argument is option or not.
     *
     * @param string $arg
     *
     * @return bool
     */
    protected function isOption($arg)
    {
        return (bool) preg_match("/^--\w+/", $arg);
    }

    /**
     * Check whether argument is option alias or not.
     *
     * @param string $arg
     *
     * @return bool
     */
    protected function isOptionAlias($arg)
    {
        return (bool) preg_match('/^-[a-z]+/i', $arg);
    }

    /**
     * Check whether argument is option with value or not.
     *
     * @param string $arg
     *
     * @return bool
     */
    protected function isOptionWithValue($arg)
    {
        return strpos($arg, '=') !== false;
    }

    /**
     * Validate And Resolve Arguments.
     *
     * @param string $command
     *
     * @return array resolved arguments
     */
    protected function validateAndResolveArguments($command)
    {
        $args = $this->arguments;
        $commandArgs = $this->commands[$command]['args'];
        $resolvedArgs = [];
        foreach ($commandArgs as $argName => $argOption) {
            if (!$argOption['is_optional'] and empty($args)) {
                return $this->error("Argument {$argName} is required", 25);
            }
            if ($argOption['is_array']) {
                $value = $args;
            } else {
                $value = array_shift($args) ?: $argOption['default'];
            }

            $resolvedArgs[$argName] = $value;
        }

        return $resolvedArgs;
    }

    /**
     * Validate And Resolve Options.
     *
     * @param string $command
     */
    protected function validateAndResolveOptions($command)
    {
        $options = $this->options;
        $optionsAlias = $this->optionsAlias;
        $commandOptions = $this->commands[$command]['options'];
        $resolvedOptions = $options;

        foreach ($commandOptions as $optName => $optionSetting) {
            $alias = $optionSetting['alias'];
            if ($alias and array_key_exists($alias, $optionsAlias)) {
                $value = array_key_exists($alias, $optionsAlias) ? $optionsAlias[$alias] : $optionSetting['default'];
            } else {
                $value = array_key_exists($optName, $options) ? $options[$optName] : $optionSetting['default'];
            }

            if (!$optionSetting['is_valuable']) {
                $resolvedOptions[$optName] = array_key_exists($alias, $optionsAlias) || array_key_exists($optName, $options);
            } else {
                $resolvedOptions[$optName] = $value;
            }
        }

        $this->resolvedOptions = $resolvedOptions;
    }

    /**
     * Show commands like given command.
     *
     * @param string $keyword
     */
    protected function showCommandsLike($keyword)
    {
        $matchedCommands = $this->getCommandsLike($keyword);

        if (count($matchedCommands) === 1) {
            $keys = array_keys($matchedCommands);
            $values = array_values($matchedCommands);
            $name = array_shift($keys);
            $command = array_shift($values);
            $this->line();
            if ($this->confirm($this->line()." Command '{$keyword}' is not available. Did you mean '{$name}'?")) {
                $this->execute($name);
            } else {
                $commandList = $this->commands['list']['handler'];
                $commandList(count($matchedCommands) ? $keyword : null);
            }
        } else {
            $commandList = $this->commands['list']['handler'];
            $commandList(count($matchedCommands) ? $keyword : null);
            $this->block(" Command '{$keyword}' is not available.", 'white', 'red');
        }
    }

    /**
     * Show command help.
     *
     * @param string $commandName
     */
    public function showHelp($commandName)
    {
        $command = $this->commands[$commandName];
        $maxLen = 0;
        $args = $command['args'];
        $opts = $command['options'];
        $usageArgs = [$commandName];
        $displayArgs = [];
        $displayOpts = [];
        foreach ($args as $argName => $argSetting) {
            $usageArgs[] = '['.$argName.']';
            $displayArg = $argName;
            if ($argSetting['is_optional']) {
                $displayArg .= ' (optional)';
            }
            if (strlen($displayArg) > $maxLen) {
                $maxLen = strlen($displayArg);
            }
            $displayArgs[$displayArg] = $argSetting['description'];
        }
        $usageArgs[] = '[options]';

        foreach ($opts as $optName => $optSetting) {
            $displayOpt = $optSetting['alias'] ? str_pad('-'.$optSetting['alias'].$optSetting['is_valuable'].',', 1) : str_repeat(' ', 1);
            $displayOpt .= '--'.$optName;
            if (strlen($displayOpt) > $maxLen) {
                $maxLen = strlen($displayOpt);
            }
            $displayOpts[$displayOpt] = $optSetting['description'];
        }

        $pad = $maxLen + 3;
        $this->writeln(' '.$command['description']);
        $this->writeln($this->color->wrap(' Usage:', 'purple'));
        $this->writeln('');
        $this->writeln('  '.implode(' ', $usageArgs));
        $this->writeln('');
        $this->writeln($this->color->wrap(' Arguments: ', 'purple'));
        foreach ($displayArgs as $argName => $argDesc) {
            $this->writeln('  '.$this->color->wrap($argName, 'green').str_repeat(' ', $pad - strlen($argName)).$argDesc);
        }
        $this->writeln('');
        $this->writeln($this->color->wrap(' Options: ', 'purple'));
        foreach ($displayOpts as $optName => $optDesc) {
            $this->writeln('  '.$this->color->wrap($optName, 'green').str_repeat(' ', $pad - strlen($optName)).$optDesc);
        }
        $this->writeln('');
    }

    /**
     * Stringify value.
     */
    protected function stringify($value)
    {
        if (is_object($value)) {
            return get_class($value);
        } elseif (is_array($value)) {
            if (count($value) > 3) {
                return 'Array';
            } else {
                return implode(', ', array_map([$this, 'stringify'], $value));
            }
        } elseif (is_bool($value)) {
            return $value ? 'true' : 'false';
        } elseif (is_string($value)) {
            return '"'.addslashes($value).'"';
        } elseif (null === $value) {
            return 'null';
        } else {
            return $value;
        }
    }

    /**
     * Error Handler.
     *
     * @param Exception $exception
     */
    public function handleError(\Throwable $exception)
    {
        //$exception = new Exception;
        $indent = str_repeat(' ', 2);
        //$class = get_class($exception);
        if (get_class($exception) === 'BiuradPHP\Toolbox\ConsoleLite\Exception\JetErrorException') {
            $class = 'Application Eexception';
        } elseif (get_class($exception) === 'BiuradPHP\Toolbox\ConsoleLite\Exception\DeprecatedException') {
            $class = 'Deprecated Exception';
        } else {
            $class = get_class($exception);
        }
        $file = $exception->getFile();
        $line = $exception->getLine();
        $filepath = function ($file) {
            return str_replace(dirname(__DIR__).DIRECTORY_SEPARATOR, '', $file);
        };
        $message = $exception->getMessage();

        $this->block($indent."Whoops! You got an {$class}"."\n\n".$indent.$this->style($message, 'light_red'));

        $this->write(
            $indent.'File: '.$filepath($file)
                .PHP_EOL
                .$indent.'Line: '.$line
                .PHP_EOL,
            'dark_gray'
        );

        $traces = $exception->getTrace();
        $count = count($traces);
        $traceFunction = function ($trace) {
            $args = implode(', ', array_map([$this, 'stringify'], $trace['args']));
            if ($trace['function'] == '{closure}') {
                return 'Closure('.$args.')';
            } elseif (!isset($trace['class'])) {
                return $trace['function'].'('.$args.')';
            } else {
                return $trace['class'].$trace['type'].$trace['function'].'('.$args.')';
            }
        };
        $x = $count > 9 ? 2 : 1;

        $this->line(2);
        $this->writeln($indent.'Traces:', 'light_red');
        $this->line();
        foreach ($traces as $i => $trace) {
            $space = str_repeat(' ', $x + 2);
            $no = str_pad($count - $i, $x, ' ', STR_PAD_LEFT);
            $func = $traceFunction($trace);
            $file = isset($trace['file']) ? $filepath($trace['file']) : 'unknown';
            $line = isset($trace['line']) ? $trace['line'] : 'unknown';
            $this->writeln("{$indent}{$no}) {$func}");
            $this->writeln("{$indent}{$space}File: {$file}", 'dark_gray');
            $this->writeln("{$indent}{$space}Line: {$line}", 'dark_gray');
            $this->line();
        }
    }
}
