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

namespace Radion\Component\Console\Command;

use Radion\Component\Console\Command;

class CommandList extends Command
{
    protected $signature = 'list {keyword?}';

    protected $description = 'Show available commands';

    public function handle($keyword)
    {
        $count = 0;
        $maxLen = 0;
        $time = microtime(true);

        if ($keyword) {
            $commands = $this->getCommandsLike($keyword);
            $this->block(" Here are commands like '{$keyword}': ", 'white', 'magenta');
        } else {
            $commands = $this->getRegisteredCommands();
            $this->writeln($this->color('Console Lite', 'green').$this->line(2).'Read more at https://tuts.biurad.ml/consolelite.md');
            if (PHP_OS == 'Linux') {
                $this->block('The Application is been runned in Linux environment', 'light_cyan', 'black');
            } elseif ($this->isWindows()) {
                $this->block('The Application is been runned in Windows environment', 'light_cyan', 'black');
            } else {
                $this->block('The Application is been runned in an Undetermined environment', 'light_cyan', 'black');
            }
            $this->writeln($this->color(' Available Commands: ', 'purple').$this->line());
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

        $this->line(1,true);
        $this->writeln(" Type '".$this->style('php '.$this->getFilename().' [command] --help or -h', 'purple')."' for usage information");
        $this->line();
        $this->writeln(" Type '".$this->style('php '.$this->getFilename().' [command] --no-color or -n', 'purple')."' to disable color");
        $this->line(1,true);
    }
}
