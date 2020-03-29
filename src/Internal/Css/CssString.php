<?php

namespace CodeDistortion\RelCss\Internal\Css;

/**
 * Represent a source of css-definitions - from a string.
 */
class CssString implements CssInterface
{
    /**
     * The css-definitions as a string.
     *
     * @var string
     */
    protected $content = '';

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
     * @param string $content The css-selectors as a string.
     */
    public function __construct(string $content)
    {
        $this->content = $content;
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
        return $this->content;
    }

    /**
     * Generate a hash based on the content.
     *
     * @return string
     */
    public function hash(): string
    {
        if (is_null($this->hash)) {
            $this->hash = md5($this->content);
        }
        return $this->hash;
    }
}
