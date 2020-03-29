<?php

namespace CodeDistortion\RelCss\Internal\Html;

use CodeDistortion\RelCss\Internal\HasFilesystemTrait;
use Throwable;

/**
 * Represent a source of html that needs css - read from a file.
 */
class HtmlFile implements HtmlInterface
{
    use HasFilesystemTrait;



    /**
     * The path of the css file.
     *
     * @var string
     */
    protected $path = '';



    /**
     * Constructor.
     *
     * @param string $path The path of the html file.
     */
    public function __construct(string $path)
    {
        $this->path = $path;
    }

    /**
     * Return the html as a string.
     *
     * @return string
     */
    public function html(): string
    {
        return $this->filesystem->get($this->path);
    }
}
