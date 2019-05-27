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

namespace BiuradPHP\Toolbox\ConsoleLite;

use Psr\Log\LoggerInterface;

/**
 * Class PSR3CLI.
 *
 * The same as CLI, but implements the PSR-3 logger interface
 */
abstract class PSR3 extends Command implements LoggerInterface
{
}
