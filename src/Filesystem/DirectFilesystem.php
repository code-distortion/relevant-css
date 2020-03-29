<?php

namespace CodeDistortion\RelCss\Filesystem;

use CodeDistortion\RelCss\Exceptions\FilesystemException;
use Throwable;

/**
 * Access the filesystem directly.
 */
class DirectFilesystem implements FilesystemInterface
{
    /**
     * Determine if a file or directory exists.
     *
     * @param string $path The file to check.
     * @return boolean
     */
    public function exists(string $path): bool
    {
        return file_exists($path);
    }

    /**
     * Return the contents of the given file.
     *
     * @param string $path The file to use.
     * @return mixed
     * @throws FilesystemException Thrown when the file doesn't exist or can't be read from.
     */
    public function get(string $path)
    {
        if (!file_exists($path)) {
            throw FilesystemException::fileNotFound($path);
        }
        try {
            return file_get_contents($path);
        } catch (Throwable $e) {
            throw FilesystemException::couldNotReadFromFile($path);
        }
    }

    /**
     * Get the returned value of a file.
     *
     * @param string $path The file to read from.
     * @return mixed
     * @throws FilesystemException Thrown when the file does not exist.
     */
    public function getRequire(string $path)
    {
        if ($this->exists($path)) {
            return require $path;
        }
        throw FilesystemException::fileNotFound($path);
    }

    /**
     * Get the MD5 hash of the file at the given path.
     *
     * @param string $path The file to read from.
     * @return string
     * @throws FilesystemException Thrown when the file does not exist.
     */
    public function hash(string $path): string
    {
        if (file_exists($path)) {
            return (string) md5_file($path);
        }
        throw FilesystemException::fileNotFound($path);
    }

    /**
     * Write the given content to the specified file.
     *
     * @param string $path    The file to write to.
     * @param string $content The content to put into the file.
     * @return boolean
     * @throws FilesystemException Thrown when the file cannot be written to.
     */
    public function put(string $path, string $content): bool
    {
        try {
            return (file_put_contents($path, $content) !== false);
        } catch (Throwable $e) {
            throw FilesystemException::cannotWriteToFile($path);
        }
    }
}
