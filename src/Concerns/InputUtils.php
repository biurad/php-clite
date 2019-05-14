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

namespace Radion\Component\Console\Concerns;

use RuntimeException;

trait InputUtils
{
    protected $questionSuffix = "\n> ";

    /**
     * Asking question.
     *
     * @param string $message
     * @param string $fgColor
     * @param string $bgColor
     */
    public function ask($question, $default = null)
    {
        if ($default) {
            $question = $question.' '.$this->style("[{$default}]", 'green');
        }

        $this->write($question.$this->questionSuffix, 'light_blue');

        $handle = fopen('php://stdin', 'r');
        $answer = trim(fgets($handle));
        fclose($handle);

        return $answer ?: $default;
    }

    /**
     * Asking secret question.
     *
     * @param string $message
     * @param string $fgColor
     * @param string $bgColor
     */
    public function askSecret($question, $default = null)
    {
        if ($default) {
            $question = $question.' '.$this->style("[{$default}]", 'green');
        }

        $this->write($question.$this->questionSuffix);

        if ($this->isWindows()) {
            $exe = __DIR__.'/../../bin/hiddeninput.exe';
            // handle code running from a phar
            if ('phar:' === substr(__FILE__, 0, 5)) {
                $tmpExe = sys_get_temp_dir().'/hiddeninput.exe';
                copy($exe, $tmpExe);
                $exe = $tmpExe;
            }
            $value = rtrim(shell_exec($exe));
            $this->writeln('');
            if (isset($tmpExe)) {
                unlink($tmpExe);
            }

            return $value ?: $default;
        }

        if ($this->hasSttyAvailable()) {
            $sttyMode = shell_exec('stty -g');
            shell_exec('stty -echo');
            $handle = fopen('php://stdin', 'r');
            $value = fgets($handle, 4096);
            shell_exec(sprintf('stty %s', $sttyMode));
            fclose($handle);
            if (false === $value) {
                throw new RuntimeException('Aborted');
            }
            $value = trim($value);
            $this->writeln('');

            return $value ?: $default;
        }

        if (false !== $shell = $this->getShell()) {
            $readCmd = $shell === 'csh' ? 'set mypassword = $<' : 'read -r mypassword';
            $command = sprintf("/usr/bin/env %s -c 'stty -echo; %s; stty echo; echo \$mypassword'", $shell, $readCmd);
            $value = rtrim(shell_exec($command));
            $this->writeln('');

            return $value ?: $default;
        }

        throw new RuntimeException('Unable to hide the response.');
    }

    /**
     * Input confirmation.
     *
     * @param string $message
     * @param string $fgColor
     * @param string $bgColor
     */
    public function confirm($question, $default = false)
    {
        $availableAnswers = [
            'yes' => true,
            'no'  => false,
            'y'   => true,
            'n'   => false,
        ];

        $result = null;
        do {
            if ($default) {
                $suffix = $this->style('[', 'dark_gray').$this->style('Y', 'green').$this->style('/n]', 'dark_gray');
            } else {
                $suffix = $this->style('[y/', 'dark_gray').$this->style('N', 'green').$this->style(']', 'dark_gray');
            }
            $answer = $this->ask($question.' '.$suffix) ?: ($default ? 'y' : 'n');

            if (!isset($availableAnswers[$answer])) {
                $this->block('Please type: (y/n) or (yes/no)', 'white', 'red', 30);
            } else {
                $result = $availableAnswers[$answer];
            }
        } while (is_null($result));

        return $availableAnswers[$answer];
    }

    /**
     * Input choice for two only.
     */
    public function choice($question, array $available, $choice1, $choice2, $errormessage = 'No Choice Selected')
    {
        $availableAnswers = $available;

        $default = false;
        $result = null;
        do {
            if ($default) {
                $suffix = $this->style('[', 'dark_gray').$this->style(ucwords($choice1), 'green').$this->style("/{$choice2}]", 'dark_gray');
            } else {
                $suffix = $this->style("[{$choice1}/", 'dark_gray').$this->style(ucwords($choice2), 'green').$this->style(']', 'dark_gray');
            }
            $answer = $this->ask($question.' '.$suffix) ?: ($default ? $choice1 : $choice2);

            if (!isset($availableAnswers[$answer])) {
                $this->error($errormessage);
            } else {
                $result = $availableAnswers[$answer];
            }
        } while (is_null($result));

        return $availableAnswers[$answer];
    }
}
