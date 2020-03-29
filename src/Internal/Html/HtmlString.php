<?php

namespace CodeDistortion\RelCss\Internal\Html;

/**
 * Represent a source of html that needs css - from a string.
 */
class HtmlString implements HtmlInterface
{
    /**
     * The css-definitions as a string.
     *
     * @var string
     */
    protected $html = '';



    /**
     * Constructor.
     *
     * @param string $html The html as a string.
     */
    public function __construct(string $html)
    {
        $this->html = $html;
    }

    /**
     * Return the html as a string.
     *
     * @return string
     */
    public function html(): string
    {
        return $this->html;
    }
}
