<?php

/**
 * The Biurad Library Autoload via cli
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

namespace BiuradPHP\Toolbox\ConsoleLite\Concerns;

trait FileUtils
{
    /**
     * Get relative path between target and base path. If path isn't relative, return full path.
     *
     * @param string       $path
     * @param mixed|string $base
     *
     * @return string
     */
    public static function getRelativePath($path, $base = null)
    {
        if (null !== $base) {
            $base = preg_replace('![\\\/]+!', '/', $base);
            $path = preg_replace('![\\\/]+!', '/', $path);
            if (strpos($path, $base) === 0) {
                $path = ltrim(substr($path, strlen($base)), '/');
            }
        }

        return $path;
    }

    /**
     * Get relative path between target and base path. If path isn't relative, return full path.
     *
     * @param  string  $path
     * @param  string  $base
     * @return string
     */
    public function getRelativePathDotDot($path, $base)
    {
        // Normalize paths.
        $base = preg_replace('![\\\/]+!', '/', $base);
        $path = preg_replace('![\\\/]+!', '/', $path);

        if ($path === $base) {
            return '';
        }

        $baseParts = explode('/', ltrim($base, '/'));
        $pathParts = explode('/', ltrim($path, '/'));

        array_pop($baseParts);
        $lastPart = array_pop($pathParts);
        foreach ($baseParts as $i => $directory) {
            if (isset($pathParts[$i]) && $pathParts[$i] === $directory) {
                unset($baseParts[$i], $pathParts[$i]);
            } else {
                break;
            }
        }
        $pathParts[] = $lastPart;
        $path = str_repeat('../', count($baseParts)) . implode('/', $pathParts);

        return '' === $path
        || strpos($path, '/') === 0
        || false !== ($colonPos = strpos($path, ':')) && ($colonPos < ($slashPos = strpos($path, '/')) || false === $slashPos)
            ? "./$path" : $path;
    }


    /**
     * Get the contents of a file.
     *
     * @param  string  $path
     * @param  bool  $lock
     * @return string
     *
     * @throws \BiuradPHP\Toolbox\ConsoleLite\Exception\JetErrorException
     */
    public function f_get($path, $lock = false)
    {
        if (is_file($path)) {
            return $lock ? $this->sharedGet($path) : file_get_contents($path);
        }

        throw new \BiuradPHP\Toolbox\ConsoleLite\Exception\JetErrorException("File does not exist at path {$path}");
    }

    /**
     * Get contents of a file with shared access.
     *
     * @param  string  $path
     * @return string
     */
    public function f_sharedGet($path)
    {
        $contents = '';

        $handle = fopen($path, 'rb');

        if ($handle) {
            try {
                if (flock($handle, LOCK_SH)) {
                    clearstatcache(true, $path);

                    $contents = fread($handle, $this->size($path) ?: 1);

                    flock($handle, LOCK_UN);
                }
            } finally {
                fclose($handle);
            }
        }

        return $contents;
    }

    /**
     * Write the contents of a file.
     *
     * @param  string  $path
     * @param  string  $contents
     * @param  bool  $lock
     * @return int|bool
     */
    public function f_put($path, $contents, $lock = false)
    {
        return file_put_contents($path, $contents, $lock ? LOCK_EX : 0);
    }

    /**
     * Scan a directory
     *
     * @param   string  $path
     * @return string
     */
     public function f_scan($path)
     {
         return \scandir($path);
     }

     /**
     * Is directory
     *
     * @param   string  $path
     * @return string
     */
     public function f_is_dir($path)
     {
         return \is_dir($path);
     }

     /**
     * Is File
     *
     * @param   string  $path
     * @return string
     */
     public function f_is_file($path)
     {
         return \is_file($path);
     }

     /**
     * Is dirname
     *
     * @param   string  $path
     * @return string
     */
     public function f_dirname($path)
     {
         return \dirname($path);
     }

     /**
     * File Exists
     *
     * @param   string  $path
     * @return string
     */
     public function f_file_exists($path)
     {
         return \file_exists($path);
     }

     /**
     * File Exists
     *
     * @param   string  $path
     * @return string
     */
     public function f_basename($path)
     {
         return \basename($path);
     }
}
