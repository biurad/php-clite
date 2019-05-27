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

namespace BiuradPHP\Toolbox\ConsoleLite\Exception;

/**
 * JetError.
 *
 * The error handle for both comandline and
 * coming soon web version.
 *
 * @author Divine Niiquaye <hello@biuhub.net>
 */
class JetErrorException extends \Exception implements \Throwable
{
    const OPTION = 'option';
    const COMMAND = 'command';
    
    public static function deprecated($name = null, $replace = null, $type = null)
    {
        if (self::OPTION === $type) {
            throw new DeprecatedException(sprintf("Using the '{$name}' option is deprecated since last released version, use '{$replace}' instead."), E_USER_DEPRECATED);
        } elseif (self::COMMAND === $type) {
            throw new DeprecatedException(sprintf("Using the command '{$name}' is deprecated since last released version, use '{$replace}' instead."), E_USER_DEPRECATED);
        } else {
            throw new DeprecatedException(sprintf("Using the '{$name}' is deprecated since last released version, use '{$replace}' instead."), E_USER_DEPRECATED);
        }
    }
}
