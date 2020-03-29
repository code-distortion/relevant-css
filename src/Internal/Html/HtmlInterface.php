<?php

namespace CodeDistortion\RelCss\Internal\Html;

/**
 * Represent a source of html that needs css.
 */
interface HtmlInterface
{

    /**
     * Return the html as a string.
     *
     * @return string
     */
    public function html(): string;
}
