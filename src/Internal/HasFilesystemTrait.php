<?php

namespace CodeDistortion\RelCss\Internal;

use CodeDistortion\RelCss\Filesystem\FilesystemInterface;

/**
 * Add the filesystem object to classes.
 */
trait HasFilesystemTrait
{
    /**
     * Used to access the filesystem.
     *
     * @var FilesystemInterface
     */
    protected $filesystem;



    /**
     * Let the caller specify the filesystem to use.
     *
     * @param FilesystemInterface $filesystem The filesystem to use.
     * @return static
     */
    public function filesystem(FilesystemInterface $filesystem): self
    {
        $this->filesystem = $filesystem;
        return $this; // chainable
    }
}
