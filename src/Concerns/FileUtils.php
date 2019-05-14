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

namespace Radion\Component\Console\Concerns;

class FileUtils
{
    /**
     * Get relative path between target and base path. If path isn't relative, return full path.
     *
     * @param string       $path
     * @param mixed|string $base
     *
     * @return string
     */
    public static function getRelativePath($path, $base = RADION_ROOT)
    {
        if ($base) {
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
    public static function getRelativePathDotDot($path, $base)
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
     * Shift first directory out of the path.
     *
     * @param string $path
     * @return string
     */
    public static function shift(&$path)
    {
        $parts = explode('/', trim($path, '/'), 2);
        $result = array_shift($parts);
        $path = array_shift($parts);

        return $result ?: null;
    }

    /**
     * Recursively copy directory in filesystem.
     *
     * @param  string $source
     * @param  string $target
     * @param  string $ignore  Ignore files matching pattern (regular expression).
     * @throws \RuntimeException
     */
    public static function copy($source, $target, $ignore = null)
    {
        $source = rtrim($source, '\\/');
        $target = rtrim($target, '\\/');

        if (!is_dir($source)) {
            throw new \RuntimeException('Cannot copy non-existing folder.');
        }

        // Make sure that path to the target exists before copying.
        self::create($target);

        $success = true;

        // Go through all sub-directories and copy everything.
        $files = $source;
        foreach ($files as $file) {
            if ($ignore && preg_match($ignore, $file)) {
                continue;
            }
            $src = $source .'/'. $file;
            $dst = $target .'/'. $file;

            if (is_dir($src)) {
                // Create current directory (if it doesn't exist).
                if (!is_dir($dst)) {
                    $success &= @mkdir($dst, 0777, true);
                }
            } else {
                // Or copy current file.
                $success &= @copy($src, $dst);
            }
        }

        if (!$success) {
            $error = error_get_last();
            throw new \RuntimeException($error['message'] ?? 'Unknown error');
        }

        // Make sure that the change will be detected when caching.
        @touch(dirname($target));
    }

    /**
     * Move directory in filesystem.
     *
     * @param  string $source
     * @param  string $target
     * @throws \RuntimeException
     */
    public static function move($source, $target)
    {
        if (!file_exists($source) || !is_dir($source)) {
            // Rename fails if source folder does not exist.
            throw new \RuntimeException('Cannot move non-existing folder.');
        }

        // Don't do anything if the source is the same as the new target
        if ($source === $target) {
            return;
        }

        if (file_exists($target)) {
            // Rename fails if target folder exists.
            throw new \RuntimeException('Cannot move files to existing folder/file.');
        }

        // Make sure that path to the target exists before moving.
        self::create(dirname($target));

        // Silence warnings (chmod failed etc).
        @rename($source, $target);

        // Rename function can fail while still succeeding, so let's check if the folder exists.
        if (!file_exists($target) || !is_dir($target)) {
            // In some rare cases rename() creates file, not a folder. Get rid of it.
            if (file_exists($target)) {
                @unlink($target);
            }
            // Rename doesn't support moving folders across filesystems. Use copy instead.
            self::copy($source, $target);
            self::delete($source);
        }

        // Make sure that the change will be detected when caching.
        @touch(dirname($source));
        @touch(dirname($target));
        @touch($target);
    }

    /**
     * Recursively delete directory from filesystem.
     *
     * @param  string $target
     * @param  bool   $include_target
     * @return bool
     * @throws \RuntimeException
     */
    public static function delete($target, $include_target = true)
    {
        if (!is_dir($target)) {
            return false;
        }

        $success = self::doDelete($target, $include_target);

        if (!$success) {
            $error = error_get_last();
            throw new \RuntimeException($error['message']);
        }

        // Make sure that the change will be detected when caching.
        if ($include_target) {
            @touch(dirname($target));
        } else {
            @touch($target);
        }

        return $success;
    }

    /**
     * @param  string  $folder
     * @throws \RuntimeException
     */
    public static function mkdir($folder)
    {
        self::create($folder);
    }

    /**
     * @param  string  $folder
     * @throws \RuntimeException
     */
    public static function create($folder)
    {
        // Silence error for open_basedir; should fail in mkdir instead.
        if (@is_dir($folder)) {
            return;
        }

        $success = @mkdir($folder, 0777, true);

        if (!$success) {
            // Take yet another look, make sure that the folder doesn't exist.
            clearstatcache(true, $folder);
            if (!@is_dir($folder)) {
                throw new \RuntimeException(sprintf('Unable to create directory: %s', $folder));
            }
        }
    }

    /**
     * Recursive copy of one directory to another
     *
     * @param string $src
     * @param string $dest
     *
     * @return bool
     * @throws \RuntimeException
     */
    public static function rcopy($src, $dest)
    {

        // If the src is not a directory do a simple file copy
        if (!is_dir($src)) {
            copy($src, $dest);
            return true;
        }

        // If the destination directory does not exist create it
        if (!is_dir($dest)) {
            static::create($dest);
        }

        // Open the source directory to read in files
        $i = new \DirectoryIterator($src);
        /** @var \DirectoryIterator $f */
        foreach ($i as $f) {
            if ($f->isFile()) {
                copy($f->getRealPath(), "{$dest}/" . $f->getFilename());
            } else {
                if (!$f->isDot() && $f->isDir()) {
                    static::rcopy($f->getRealPath(), "{$dest}/{$f}");
                }
            }
        }
        return true;
    }

    /**
     * @param  string $folder
     * @param  bool   $include_target
     * @return bool
     * @internal
     */
    protected static function doDelete($folder, $include_target = true)
    {
        // Special case for symbolic links.
        if ($include_target && is_link($folder)) {
            return @unlink($folder);
        }

        // Go through all items in filesystem and recursively remove everything.
        $files = array_diff(scandir($folder, SCANDIR_SORT_NONE), array('.', '..'));
        foreach ($files as $file) {
            $path = "{$folder}/{$file}";
            is_dir($path) ? self::doDelete($path) : @unlink($path);
        }

        return $include_target ? @rmdir($folder) : true;
    }
}
