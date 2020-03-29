<?php

namespace CodeDistortion\RelCss\Exceptions;

use Exception;

/**
 * The main RelevantCss exception class.
 */
class RelevantCssException extends Exception
{
    /**
     * Return a new instance when a directive value is invalid (ie. the value passed to "@relevantCss" in a blade
     * template).
     *
     * @param string $expression The invalid value.
     * @return static
     */
    public static function invalidDirectiveValue(string $expression): self
    {
        return new static('Invalid directive value "'.$expression.'"');
    }
}
