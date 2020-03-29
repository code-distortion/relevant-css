<?php

namespace CodeDistortion\RelCss\Internal;

/**
 * A collection of support methods.
 */
class ArraySupport
{
    /**
     * Build an array with the given $values as keys.
     *
     * @param mixed[] $values The values to use as keys.
     * @param mixed   $value  The value to give each element.
     * @return mixed[]
     */
    public static function buildArrayFill(array $values, $value)
    {
        return (array) array_combine(
            $values,
            array_fill(0, count($values), $value)
        );
    }
}
