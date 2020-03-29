<?php

namespace CodeDistortion\RelCss\Exceptions;

/**
 * RelevantCss exceptions related to the filesystem.
 */
class FilesystemException extends RelevantCssException
{
    /**
     * Return a new instance when a file does not exist.
     *
     * @param string $path The path of the file being read.
     * @return static
     */
    public static function fileNotFound(string $path): self
    {
        return new static('The file "'.$path.'" does not exist');
    }

    /**
     * Return a new instance when a file could not be read from.
     *
     * @param string $path The path of the file being read.
     * @return static
     */
    public static function couldNotReadFromFile(string $path): self
    {
        return new static('Could not read from "'.$path.'"');
    }

    /**
     * Return a new instance when a file could not written to.
     *
     * @param string $path The path of the file being written to.
     * @return static
     */
    public static function cannotWriteToFile(string $path): self
    {
        return new static('Could not write to file "'.$path.'"');
    }
}
