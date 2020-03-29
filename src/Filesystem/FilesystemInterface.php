<?php

namespace CodeDistortion\RelCss\Filesystem;

use CodeDistortion\RelCss\Exceptions\FilesystemException;

interface FilesystemInterface
{
    /**
     * Return whether the file exists or not.
     *
     * @param string $path The file to check.
     * @return boolean
     */
    public function exists(string $path):  bool;

    /**
     * Return the contents of the given file.
     *
     * @param string $path The file to use.
     * @return mixed
     * @throws FilesystemException Thrown when the file doesn't exist or can't be read from.
     */
    public function get(string $path);

    /**
     * Get the returned value of a file.
     *
     * @param string $path The file to read from.
     * @return mixed
     * @throws FilesystemException Thrown when the file does not exist.
     */
    public function getRequire(string $path);

    /**
     * Get the MD5 hash of the file at the given path.
     *
     * @param string $path The file to read from.
     * @return string
     * @throws FilesystemException Thrown when the file does not exist.
     */
    public function hash(string $path): string;

    /**
     * Write the given content to the specified file.
     *
     * @param string $path    The file to write to.
     * @param string $content The content to put into the file.
     * @return boolean
     * @throws FilesystemException Thrown when the file cannot be written to.
     */
    public function put(string $path, string $content): bool;
}
