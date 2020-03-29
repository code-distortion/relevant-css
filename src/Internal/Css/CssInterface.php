<?php

namespace CodeDistortion\RelCss\Internal\Css;

/**
 * Represent a source of css-definitions.
 */
interface CssInterface
{
    /**
     * Let the caller specify whether the hash should detect changes to the content or not.
     *
     * @param boolean $autoReCache Whether the hash should detect changes to the content or not.
     * @return static
     */
    public function autoReCache(bool $autoReCache = true);

    /**
     * Return the css-definitions as a string.
     *
     * @return string
     */
    public function cssDefinitions(): string;

    /**
     * Generate a hash based on the content.
     *
     * @return string
     */
    public function hash(): string;
}
