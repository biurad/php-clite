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
 */

namespace BiuradPHP\Toolbox\ConsoleLite\Command;

use BiuradPHP\Toolbox\ConsoleLite\Command;

class CommandList extends Command
{
    protected $signature = 'list {keyword?}';

    protected $description = 'Show available commands';

    public function handle($keyword)
    {
        $count = 0;
        $maxLen = 0;

        if ($keyword) {
            $commands = $this->getCommandsLike($keyword);
            $this->block("Here are commands like '{$keyword}': ", 'white', 'magenta');
            $this->line();
        } else {
            $commands = $this->getRegisteredCommands();
            $this->writeln('Read more at https://tuts.biurad.ml/consolelite.md');
            if ($this->isLinux()) {
                $this->block('The Application is been runned in Linux environment', 'light_cyan', 'black');
            } elseif ($this->isWindows()) {
                $this->block('The Application is been runned in Windows environment', 'light_cyan', 'black');
            } else {
                $this->block('The Application is been runned in an Undetermined environment', 'light_cyan', 'black');
            }
            $this->writeln($this->color('Available Commands: ', 'purple'));
            $this->line();
        }

        ksort($commands);

        foreach (array_keys($commands) as $name) {
            if (strlen($name) > $maxLen) {
                $maxLen = strlen($name);
            }
        }
        $pad = $maxLen + 3;

        foreach ($commands as $name => $command) {
            $no = ++$count.') ';
            $this->write(str_repeat(' ', 4 - strlen($no)).$this->color($no, 'dark_gray'));
            $this->write($this->color($name, 'green').str_repeat(' ', $pad - strlen($name)));
            $this->writeln($command['description']);
            $this->writeln('');
        }

        $this->writeln('Default Usage: ', 'magenta');
        $this->helpblock(' Type: php '.basename($this->getFilename()).' [command] --help or -h', 'For usage information about a command', '*', '50%');
        $this->helpblock(' Type: php '.basename($this->getFilename()).' [command] --no-color or -n', 'To disable color', '*', '50%');
        $this->helpblock(' Type: php '.basename($this->getFilename()).' [command] --color or -c', 'To enable color', '*', '50%');
    }
}
