<?php

namespace CodeDistortion\RelCss\Internal\Css;

use CodeDistortion\RelCss\Internal\HasFilesystemTrait;
use Throwable;

/**
 * Represent a source of css-definitions - read from a file.
 */
class CssFile implements CssInterface
{
    use HasFilesystemTrait;



    /**
     * The path of the css file.
     *
     * @var string
     */
    protected $path = '';

    /**
     * Whether the hash should detect changes to the content or not.
     *
     * @var boolean
     */
    protected $autoReCache = true;

    /**
     * A cache of the hash.
     *
     * @var string|null
     */
    protected $hash = null;



    /**
     * Constructor.
     *
     * @param string $path The path of the css file.
     */
    public function __construct(string $path)
    {
        $this->path = $path;
    }

    /**
     * Specify whether the hash should detect changes to the content or not.
     *
     * @param boolean $autoReCache Whether the hash should detect changes to the content or not.
     * @return static
     */
    public function autoReCache(bool $autoReCache = true)
    {
        $this->autoReCache = $autoReCache;
        return $this;
    }

    /**
     * Return the css-definitions as a string.
     *
     * @return string
     */
    public function cssDefinitions(): string
    {
        return $this->filesystem->get($this->path);
    }

    /**
     * Generate a hash based on the content.
     *
     * @return string
     */
    public function hash(): string
    {
        if (is_null($this->hash)) {
            try {
                $this->hash = ($this->autoReCache
                    ? $this->filesystem->hash($this->path)
                    : $this->path);
            } catch (Throwable $e) {
                $this->hash = '';
            }
            return $this->hash;
        }
        return '';
    }
}
