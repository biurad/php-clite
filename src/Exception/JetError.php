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

namespace Radion\Toolbox\ConsoleLite\Exception;

/**
 * JetError.
 *
 * The error handle for both comandline and
 * coming soon web version.
 *
 * @author Divine Niiquaye <hello@biuhub.net>
 */
class JetError extends \Exception implements \Throwable
{
    public function run()
    {
        set_exception_handler('Radion\Toolbox\ConsoleLite\Application::exception');
    }
}
