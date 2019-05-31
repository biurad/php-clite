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
use BiuradPHP\Toolbox\ConsoleLite\Formatter;

class CommandAbout extends Command
{
    protected $signature = 'about';

    protected $description = 'Displays information about the current project';

    protected $formatter;

    const VERSION = '1.4';

    public function __construct()
    {
        $this->formatter = new Formatter();
    }

    public function handle()
    {
        $this->formatter->setBorder(' | '); //set border
        $this->formatter->setMaxWidth('95');
        $this->line(1, true);

        // set the header option
        $this->write($this->formatter->format(
            ['20%', '*'],
            ['Console Lite', '']
        ));
        $this->line(1, true);

        // list of first ([0]name, [1]note or description)
        $header_section = [
            ['About', 'See the ReadMe.md for more info'],
            ['Version', $this::VERSION.' latest built'],
            ['Copyright', 'Divine Niiquaye hello@biuhub.net'],
        ];
        foreach ($header_section as $first) {
            $this->write($this->formatter->format(
                ['20%', '75%'],
                [$first[0], $first[1]]
            ));
        }
        // create a horizontal line
        $this->line(1, true);

        // set the header option
        $this->write($this->formatter->format(
            ['20%', '*'],
            ['App Check', '']
        ));

        // create a horinzontal line
        $this->line(1, true);

        //set options and settings
        $kernel = $this->getType();
        $rootpath = realpath(dirname(__FILE__));
        $memory = memory_get_usage();

        // list of second ([0]name, [1]note or description)
        $app_section = [
            ['Type', \get_class($kernel)],
            ['Charset', 'UTF-8'],
            ['File InUse', $this->getFilename()],
            ['App File Size', $this->formatter->formatPath(getcwd(), $rootpath).' '.$this->formatter->formatFileSize($rootpath) ?: 'Could not read?'],
            ['Memory Usage', $this->formatter->formatMemory($memory)],
            ['PHP OS', PHP_OS.' built -> '.PHP_WINDOWS_VERSION_BUILD],
            ['PHP Version', PHP_VERSION],
            ['Architecture', (PHP_INT_SIZE * 8).' bits'],
            ['Intl locale', class_exists('Locale', false) && \Locale::getDefault() ? \Locale::getDefault() : 'n/a'],
            ['Timezone', date_default_timezone_get().' ('.(new \DateTime())->format(\DateTime::W3C).')'],
            ['MBString PHP EXT', \extension_loaded('mbstring') ? 'true' : 'false'],
            ['Mcrypt PHP Func', function_exists('mcrypt_decrypt') && function_exists('mcrypt_encrypt') && function_exists('mcrypt_decrypt') && function_exists('mcrypt_encrypt') ? 'exists' : 'not found'],
            ['JSon PHP EXT', \extension_loaded('json') ? 'true' : 'false'],
            ['OPcache PHP EXT', \extension_loaded('Zend OPcache') && filter_var(ini_get('opcache.enable'), FILTER_VALIDATE_BOOLEAN) ? 'true' : 'false'],
            ['APCu PHP EXT', \extension_loaded('apcu') && filter_var(ini_get('apc.enabled'), FILTER_VALIDATE_BOOLEAN) ? 'true' : 'false'],
            ['Xdebug PHP EXT', \extension_loaded('xdebug') ? 'true' : 'false'],
        ];
        foreach ($app_section as $second) {
            $this->write($this->formatter->format(
                ['20%', '75%'],
                [$second[0], $second[1]]
            ));
        }
        // create a horizontal line
        $this->line(1, true);
    }
}
