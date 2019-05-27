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

use Exception;

/**
 * Class Colors.
 *
 * Handles color output on (Linux) terminals
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 * @author Divine Niiquaye <hello@biuhub.net>
 */
class Colors
{
    // these constants make IDE autocompletion easier, but color names can also be passed as strings
    const C_RESET       = 'reset';
    const C_BLACK       = 'black';
    const C_DARKGRAY    = 'darkgray';
    const C_BLUE        = 'blue';
    const C_LIGHTBLUE   = 'lightblue';
    const C_GREEN       = 'green';
    const C_LIGHTGREEN  = 'lightgreen';
    const C_CYAN        = 'cyan';
    const C_LIGHTCYAN   = 'lightcyan';
    const C_RED         = 'red';
    const C_LIGHTRED    = 'lightred';
    const C_PURPLE      = 'purple';
    const C_MAGENTA     = 'magenta';
    const C_LIGHTPURPLE = 'lightpurple';
    const C_BROWN       = 'brown';
    const C_YELLOW      = 'yellow';
    const C_LIGHTGRAY   = 'lightgray';
    const C_WHITE       = 'white';

    /** @var array known color names */
    protected $colors = [
        self::C_RESET       => "0",
        self::C_BLACK       => "0;30",
        self::C_DARKGRAY    => "1;30",
        self::C_BLUE        => "0;34",
        self::C_LIGHTBLUE   => "1;34",
        self::C_GREEN       => "0;32",
        self::C_LIGHTGREEN  => "1;32",
        self::C_CYAN        => "0;36",
        self::C_LIGHTCYAN   => "1;36",
        self::C_RED         => "0;31",
        self::C_LIGHTRED    => "1;31",
        self::C_PURPLE      => "0;35",
        self::C_MAGENTA     => "0;35",
        self::C_LIGHTPURPLE => "1;35",
        self::C_BROWN       => "0;33",
        self::C_YELLOW      => "1;33",
        self::C_LIGHTGRAY   => "0;37",
        self::C_WHITE       => "1;37",
    ];

    private static $foregroundColors = [
        'black'        => self::C_BLACK,
        'dark_gray'    => self::C_DARKGRAY,
        'blue'         => self::C_BLUE,
        'light_blue'   => self::C_LIGHTBLUE,
        'green'        => self::C_GREEN,
        'light_green'  => self::C_LIGHTGREEN,
        'cyan'         => self::C_CYAN,
        'light_cyan'   => self::C_LIGHTCYAN,
        'red'          => self::C_RED,
        'light_red'    => self::C_LIGHTRED,
        'purple'       => self::C_PURPLE,
        'magenta'      => self::C_MAGENTA,
        'light_purple' => self::C_LIGHTPURPLE,
        'brown'        => self::C_BROWN,
        'yellow'       => self::C_YELLOW,
        'light_gray'   => self::C_LIGHTGRAY,
        'white'        => self::C_WHITE,
        'default'      => self::C_RESET,
    ];

    private static $backgroundColors = [
        'black'      => '40',
        'red'        => '41',
        'green'      => '42',
        'yellow'     => '43',
        'blue'       => '44',
        'magenta'    => '45',
        'cyan'       => '46',
        'light_gray' => '47',
    ];

    /** @var bool should colors be used? */
    protected $enabled = true;

    /**
     * Constructor.
     *
     * Tries to disable colors for non-terminals
     */
    public function __construct()
    {
        if (function_exists('posix_isatty') && !posix_isatty(STDOUT)) {
            $this->disable();

            return;
        }
        if (!getenv('TERM')) {
            $this->disable();

            return;
        }
        if (!$this->isWindows()) {
            $this->enable();

            return;
        }
    }

    /**
     * enable color output.
     */
    public function enable()
    {
        $this->enabled = true;
        $file = getcwd().DIRECTORY_SEPARATOR.'color.config';
        $handle = fopen($file, 'w');
        fwrite($handle, 'true');
        fclose($handle);
    }

    /**
     * disable color output.
     */
    public function disable()
    {
        $this->enabled = false;
        $file = getcwd().DIRECTORY_SEPARATOR.'color.config';
        $handle = fopen($file, 'w');
        fwrite($handle, 'false');
        fclose($handle);
    }

    /**
     * @return bool is color support enabled?
     */
    public function isEnabled()
    {
        
        if (@file_get_contents(getcwd().DIRECTORY_SEPARATOR.'color.config') == 'true') {
            return $this->enabled;
        }
    }

    public static function removecolor($string)
    {
        $string = preg_replace("/\033\[[^m]*m/", '', $string);
        @unlink(getcwd().DIRECTORY_SEPARATOR.'color.config');

        return $string;
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
     * Convenience function to print a line in a given color.
     *
     * @param string   $line    the line to print, a new line is added automatically
     * @param string   $color   one of the available color names
     * @param resource $channel file descriptor to write to
     *
     * @throws Exception
     */
    public function println($line, $color, $bgColor = null, $channel = STDOUT)
    {
        $this->set($color, $bgColor);
        fwrite($channel, rtrim($line));
        $this->reset();
    }

    /**
     * Returns the given text wrapped in the appropriate color and reset code.
     *
     * @param string $text  string to wrap
     * @param string $fgColor one of the available color names
     * @param string $bgColor one of the avialiable background color
     *
     * @throws Exception
     *
     * @return string the wrapped string
     */
    public function wrap($text, $fgColor, $bgColor = null)
    {
        // Check if given foreground color found
        if (isset(static::$foregroundColors[$fgColor])) {
            $coloredString = static::$foregroundColors[$fgColor];
        }

        // Add string and end coloring
        //$coloredString .=  $text . ($colored? $this->reset() : "");
        return $this->getColorCode($coloredString, $bgColor).$text.$this->getColorCode('reset');
    }

    /**
     * Gets the appropriate terminal code for the given color.
     *
     * @param string $color one of the available color names
     *
     * @throws \InvalidArgumentException When the color names isn't defined
     *
     * @return string color code
     */
    public function getColorCode($color, $bgColor = null)
    {
        if (!$this->isEnabled()) {
            return '';
        }

        $coloredString = '';

        // Check if given background color found
        if (isset(static::$backgroundColors[$bgColor])) {
            $coloredString .= sprintf("\033[%sm", static::$backgroundColors[$bgColor]);
        }

        // Check if given foreground color found
        if (isset($this->colors[$color])) {
            $coloredString .= sprintf("\033[%sm", $this->colors[$color]);
        }
        if (!isset($this->colors[$color])) {
            throw new \InvalidArgumentException(sprintf('Invalid foreground color specified: "%s". Expected one of (%s)', $color, implode(', ', array_keys(static::$foregroundColors))));
        }

        //return $this->colors[$color];
        return $coloredString;
    }

    /**
     * Set the given color for consecutive output.
     *
     * @param string   $color   one of the supported color names
     * @param resource $channel file descriptor to write to
     *
     * @throws Exception
     */
    public function set($color, $bgColor = null, $channel = STDOUT)
    {
        fwrite($channel, $this->getColorCode($color, $bgColor));
    }

    /**
     * reset the terminal color.
     *
     * @param resource $channel file descriptor to write to
     *
     * @throws Exception
     */
    public function reset($channel = STDOUT)
    {
        $this->set('reset', null, $channel);
    }
}
