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

namespace Radion\Component\Console;

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
    const C_RESET = 'reset';
    const C_BLACK = 'black';
    const C_DARKGRAY = 'darkgray';
    const C_BLUE = 'blue';
    const C_LIGHTBLUE = 'lightblue';
    const C_GREEN = 'green';
    const C_LIGHTGREEN = 'lightgreen';
    const C_CYAN = 'cyan';
    const C_LIGHTCYAN = 'lightcyan';
    const C_RED = 'red';
    const C_LIGHTRED = 'lightred';
    const C_PURPLE = 'purple';
    const C_LIGHTPURPLE = 'lightpurple';
    const C_BROWN = 'brown';
    const C_YELLOW = 'yellow';
    const C_LIGHTGRAY = 'lightgray';
    const C_WHITE = 'white';

    /** @var array known color names */
    protected $colors = [
        self::C_RESET       => "\33[0m",
        self::C_BLACK       => "\33[0;30m",
        self::C_DARKGRAY    => "\33[1;30m",
        self::C_BLUE        => "\33[0;34m",
        self::C_LIGHTBLUE   => "\33[1;34m",
        self::C_GREEN       => "\33[0;32m",
        self::C_LIGHTGREEN  => "\33[1;32m",
        self::C_CYAN        => "\33[0;36m",
        self::C_LIGHTCYAN   => "\33[1;36m",
        self::C_RED         => "\33[0;31m",
        self::C_LIGHTRED    => "\33[1;31m",
        self::C_PURPLE      => "\33[0;35m",
        self::C_LIGHTPURPLE => "\33[1;35m",
        self::C_BROWN       => "\33[0;33m",
        self::C_YELLOW      => "\33[1;33m",
        self::C_LIGHTGRAY   => "\33[0;37m",
        self::C_WHITE       => "\33[1;37m",
    ];

    protected $foregroundColors = [
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
        'light_purple' => self::C_LIGHTPURPLE,
        'brown'        => self::C_BROWN,
        'yellow'       => self::C_YELLOW,
        'light_gray'   => self::C_LIGHTGRAY,
        'white'        => self::C_WHITE,
        'default'      => self::C_RESET,
    ];

    protected $backgroundColors = [
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
    }

    /**
     * enable color output.
     */
    public function enable()
    {
        $this->enabled = true;
    }

    /**
     * disable color output.
     */
    public function disable()
    {
        $this->enabled = false;
    }

    /**
     * @return bool is color support enabled?
     */
    public function isEnabled()
    {
        return $this->enabled;
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
    public function ptln($line, $color, $bgColor = null, $channel = STDOUT)
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
        if (isset($this->foregroundColors[$fgColor])) {
            $coloredString = $this->foregroundColors[$fgColor];
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
     * @throws Exception
     *
     * @return string color code
     */
    public function getColorCode($color, $bgColor = null)
    {
        if (!$this->isEnabled()) {
            return '';
        }

        $coloredString = '';
        if (!isset($this->colors[$color])) {
            throw new Exception("No such color $color");
        }

        // Check if given background color found
        if (isset($this->backgroundColors[$bgColor])) {
            $coloredString .= "\033[".$this->backgroundColors[$bgColor].'m';
        }

        // Check if given foreground color found
        if (isset($this->colors[$color])) {
            $coloredString .= $this->colors[$color];
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
        $this->set('reset', $channel);
    }
}
