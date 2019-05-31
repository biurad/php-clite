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

use BiuradPHP\Toolbox\ConsoleLite\Exception\JetErrorException;

/**
 * Class TableFormatter.
 *
 * Output text in multiple columns
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 * @author Divine Niiquaye <hello@biuhub.net>
 */
class Formatter
{
    /** @var string border between columns */
    protected $border = ' ';

    /** @var int the terminal width */
    protected $max = 95;

    /** @var Colors for coloring output */
    protected $colors;

    private $value;
    private $options = [
        'rowspan' => 1,
        'colspan' => 1,
    ];

    /**
     * TableFormatter constructor.
     *
     * @param Colors|null $colors
     */
    public function __construct(Colors $colors = null)
    {
        // try to get terminal width
        $width = $this->getTerminalWidth();
        if ($width) {
            $this->max = $width - 1;
        }

        if ($colors) {
            $this->colors = $colors;
        } else {
            $this->colors = new Colors();
        }
    }

    /**
     * The currently set border (defaults to ' ').
     *
     * @return string
     */
    public function getBorder()
    {
        return $this->border;
    }

    /**
     * Set the border. The border is set between each column. Its width is
     * added to the column widths.
     *
     * @param string $border
     */
    public function setBorder($border)
    {
        $this->border = $border;
    }

    /**
     * Width of the terminal in characters.
     *
     * initially autodetected
     *
     * @return int
     */
    public function getMaxWidth()
    {
        return (int) $this->max;
    }

    /**
     * Set the width of the terminal to assume (in characters).
     *
     * @param int $max
     */
    public function setMaxWidth($max)
    {
        $this->max = (int) $max;
    }

    /**
     * Tries to figure out the width of the terminal.
     *
     * @return int terminal width, 0 if unknown
     */
    protected function getTerminalWidth()
    {
        // from environment
        if (isset($_SERVER['COLUMNS'])) {
            return (int) $_SERVER['COLUMNS'];
        }

        // via tput
        $process = proc_open('tput cols', [
            1 => ['pipe', 'w'],
            2 => ['pipe', 'w'],
        ], $pipes);
        $width = (int) stream_get_contents($pipes[1]);
        proc_close($process);

        return $width;
    }

    /**
     * Takes an array with dynamic column width and calculates the correct width.
     *
     * Column width can be given as fixed char widths, percentages and a single * width can be given
     * for taking the remaining available space. When mixing percentages and fixed widths, percentages
     * refer to the remaining space after allocating the fixed width
     *
     * @param array $columns
     *
     * @throws JetErrorException
     *
     * @return int[]
     */
    protected function calculateColLengths($columns)
    {
        $idx = 0;
        $border = $this->strlen($this->border);
        $fixed = (count($columns) - 1) * $border; // borders are used already
        $fluid = -1;

        // first pass for format check and fixed columns
        foreach ($columns as $idx => $col) {
            // handle fixed columns
            if ((string) intval($col) === (string) $col) {
                $fixed += $col;
                continue;
            }
            // check if other colums are using proper units
            if (substr($col, -1) == '%') {
                continue;
            }
            if ($col == '*') {
                // only one fluid
                if ($fluid < 0) {
                    $fluid = $idx;
                    continue;
                } else {
                    throw new JetErrorException('Only one fluid column allowed!');
                }
            }

            throw new JetErrorException("unknown column format $col");
        }

        $alloc = $fixed;
        $remain = $this->max - $alloc;

        // second pass to handle percentages
        foreach ($columns as $idx => $col) {
            if (substr($col, -1) != '%') {
                continue;
            }
            $perc = floatval($col);

            $real = (int) floor(($perc * $remain) / 100);

            $columns[$idx] = $real;
            $alloc += $real;
        }

        $remain = $this->max - $alloc;
        if ($remain < 0) {
            throw new JetErrorException('Wanted column widths exceed available space');
        }

        // assign remaining space
        if ($fluid < 0) {
            $columns[$idx] += ($remain); // add to last column
        } else {
            $columns[$fluid] = $remain;
        }

        return $columns;
    }

    /**
     * Displays text in multiple word wrapped columns.
     *
     * @param int[]    $columns list of column widths (in characters, percent or '*')
     * @param string[] $texts   list of texts for each column
     * @param array    $colors  A list of color names to use for each column. use empty string for default
     *
     * @throws JetErrorException
     *
     * @return string
     */
    public function format($columns, $texts, $colors = [])
    {
        $columns = $this->calculateColLengths($columns);

        $wrapped = [];
        $maxlen = 0;

        foreach ($columns as $col => $width) {
            $wrapped[$col] = explode("\n", $this->wordwrap($texts[$col], $width, "\n", true));
            $len = count($wrapped[$col]);
            if ($len > $maxlen) {
                $maxlen = $len;
            }
        }

        $last = count($columns) - 1;
        $out = '';
        for ($i = 0; $i < $maxlen; $i++) {
            foreach ($columns as $col => $width) {
                if (isset($wrapped[$col][$i])) {
                    $val = $wrapped[$col][$i];
                } else {
                    $val = '';
                }
                $chunk = $this->pad($val, $width);
                if (isset($colors[$col]) && $colors[$col]) {
                    $chunk = $this->colors->wrap($chunk, $colors[$col]);
                }
                $out .= $chunk;

                // border
                if ($col != $last) {
                    $out .= $this->border;
                }
            }
            $out .= "\n";
        }

        return $out;
    }

    /**
     * Pad the given string to the correct length.
     *
     * @param string $string
     * @param int    $len
     *
     * @return string
     */
    protected function pad($string, $len)
    {
        $strlen = $this->strlen($string);
        if ($strlen > $len) {
            return $string;
        }

        $pad = $len - $strlen;

        return $string.str_pad(' ', $pad, ' ');
    }

    /**
     * Measures char length in UTF-8 when possible.
     *
     * @param $string
     *
     * @return int
     */
    protected function strlen($string)
    {
        // don't count color codes
        $string = preg_replace("/\33\\[\\d+(;\\d+)?m/", '', $string);

        if (function_exists('mb_strlen')) {
            return mb_strlen($string, 'utf-8');
        }

        return strlen($string);
    }

    /**
     * @param string   $string
     * @param int      $start
     * @param int|null $length
     *
     * @return string
     */
    protected function substr($string, $start = 0, $length = null)
    {
        if (function_exists('mb_substr')) {
            return mb_substr($string, $start, $length);
        } else {
            return substr($string, $start, $length);
        }
    }

    /**
     * @param string $str
     * @param int    $width
     * @param string $break
     * @param bool   $cut
     *
     * @return string
     *
     * @see http://stackoverflow.com/a/4988494
     */
    public function wordwrap($str, $width = 75, $break = "\n", $cut = false)
    {
        $lines = explode($break, $str);
        foreach ($lines as &$line) {
            $line = rtrim($line);
            if ($this->strlen($line) <= $width) {
                continue;
            }
            $words = explode(' ', $line);
            $line = '';
            $actual = '';
            foreach ($words as $word) {
                if ($this->strlen($actual.$word) <= $width) {
                    $actual .= $word.' ';
                } else {
                    if ($actual != '') {
                        $line .= rtrim($actual).$break;
                    }
                    $actual = $word;
                    if ($cut) {
                        while ($this->strlen($actual) > $width) {
                            $line .= $this->substr($actual, 0, $width).$break;
                            $actual = $this->substr($actual, $width);
                        }
                    }
                    $actual .= ' ';
                }
            }
            $line .= trim($actual);
        }

        return implode($break, $lines);
    }

    /**
     * Returns the cell value.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->value;
    }

    /**
     * Gets number of colspan.
     *
     * @return int
     */
    public function getColspan()
    {
        return (int) $this->options['colspan'];
    }

    /**
     * Gets number of rowspan.
     *
     * @return int
     */
    public function getRowspan()
    {
        return (int) $this->options['rowspan'];
    }

    // Static Methods

    /**
     * Format Memory.
     *
     * @param mixed $memory
     *
     * @return string
     */
    public static function formatMemory($memory)
    {
        if ($memory >= 1024 * 1024 * 1024) {
            return sprintf('%.1f GiB', $memory / 1024 / 1024 / 1024);
        }

        if ($memory >= 1024 * 1024) {
            return sprintf('%.1f MB', $memory / 1024 / 1024);
        }

        if ($memory >= 1024) {
            return sprintf('%d KiB', $memory / 1024);
        }

        return sprintf('%d Bytes', $memory);
    }

    public static function formatTime($secs)
    {
        static $timeFormats = [
            [0, '< 1 second'],
            [1, '1 second'],
            [2, 'seconds', 1],
            [60, '1 minute'],
            [120, 'minutes', 60],
            [3600, '1 hour'],
            [7200, 'hours', 3600],
            [86400, '1 day'],
            [172800, 'days', 86400],
        ];

        foreach ($timeFormats as $index => $format) {
            if ($secs >= $format[0]) {
                if ((isset($timeFormats[$index + 1]) && $secs < $timeFormats[$index + 1][0])
                    || $index == \count($timeFormats) - 1
                ) {
                    if (2 == \count($format)) {
                        return $format[1];
                    }

                    return floor($secs / $format[2]).' '.$format[1];
                }
            }
        }
    }

    /**
     * Format Path.
     *
     * @param string $path
     * @param string $baseDir
     *
     * @return string
     */
    public static function formatPath(string $path, string $baseDir): string
    {
        return preg_replace('~^'.preg_quote($baseDir, '~').'~', '.', $path);
    }

    /**
     * Format FileSize.
     *
     * @param string $path
     *
     * @return string
     */
    public static function formatFileSize(string $path): string
    {
        if (is_file($path)) {
            $size = filesize($path) ?: 0;
        } else {
            $size = 0;
            foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($path, \RecursiveDirectoryIterator::SKIP_DOTS | \RecursiveDirectoryIterator::FOLLOW_SYMLINKS)) as $file) {
                $size += $file->getSize();
            }
        }

        return self::formatMemory($size);
    }
}
