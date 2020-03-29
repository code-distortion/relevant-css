<?php

namespace CodeDistortion\RelCss;

use CodeDistortion\RelCss\Internal\Html\HtmlFile;
use CodeDistortion\RelCss\Internal\Html\HtmlString;
use CodeDistortion\RelCss\Internal\HtmlMgmt;
use CodeDistortion\RelCss\Filesystem\FilesystemInterface;
use CodeDistortion\RelCss\Filesystem\DirectFilesystem;
use CodeDistortion\RelCss\Internal\HasFilesystemTrait;
use CodeDistortion\RelCss\Internal\CssMgmt;
use CodeDistortion\RelCss\Internal\Css\CssFile;
use CodeDistortion\RelCss\Internal\Css\CssString;

/**
 * Detect usages of css-selectors in content and build a customised set of css-definitions for them.
 */
class RelevantCss
{
    use HasFilesystemTrait;


    /**
     * Handles where the css-definitions come from.
     *
     * @var CssMgmt
     */
    protected $cssMgmt;

    /**
     * Handles content that needs css styling.
     *
     * @var HtmlMgmt
     */
    protected $htmlMgmt;

    /**
     * Should unused css-definitions should be removed?.
     *
     * @var boolean
     */
    protected $removeUnused = true;

    /**
     * Should the output be minified?.
     *
     * @var boolean
     */
    protected $minify = false;

    /**
     * The custom css, built based on the content.
     *
     * @var string|null
     */
    protected $outputCss = null;


    /**
     * Constructor.
     *
     * @param string              $cacheDir    The directory to cache parsed css in.
     * @param FilesystemInterface $filesystem  Used to access the filesystem.
     * @param boolean             $autoReCache When true, changes to the contents of the source css will be detected.
     */
    public function __construct(string $cacheDir = '', FilesystemInterface $filesystem = null, bool $autoReCache = true)
    {
        $this->filesystem = ($filesystem ?? new DirectFilesystem());
        $this->cssMgmt = new CssMgmt($this->filesystem, $cacheDir, $autoReCache);
        $this->htmlMgmt = new HtmlMgmt();
    }

    /**
     * Alternative constructor.
     *
     * @param string              $cacheDir    The directory to cache parsed css in.
     * @param FilesystemInterface $filesystem  Used to access the filesystem.
     * @param boolean             $autoReCache When true, changes to the contents of the source css will be detected.
     * @return static
     */
    public static function new(
        string $cacheDir = '',
        FilesystemInterface $filesystem = null,
        bool $autoReCache = true
    ): self {
        return new static($cacheDir, $filesystem, $autoReCache);
    }


    /**
     * Add css source paths (eg. a .css file).
     *
     * @param string|string[] $paths The path/s to look for css-definitions in.
     * @return static
     */
    public function cssFile($paths): self
    {
        $paths = (is_array($paths) ? $paths : [$paths]);
        foreach ($paths as $path) {
            $source = new CssFile($path);
            $source->filesystem($this->filesystem);
            $this->cssMgmt->addSource($source);
        }
        $this->outputCss = null; // force output to be regenerated
        return $this;
    }

    /**
     * Add css-definitions to use (eg. the content from a css file).
     *
     * @param string $content Css-definitions passed as a string.
     * @return static
     */
    public function cssDefinitions(string $content): self
    {
        $this->cssMgmt->addSource(new CssString($content));
        $this->outputCss = null; // force output to be regenerated
        return $this;
    }

    /**
     * Add content path/s that need styling.
     *
     * @param string|string[] $paths The path/s to look for words thing might be selectable.
     * @return static
     */
    public function fileNeedsCss($paths): self
    {
        $paths = (is_array($paths) ? $paths : [$paths]);
        foreach ($paths as $path) {
            $source = new HtmlFile($path);
            $source->filesystem($this->filesystem);
            $this->htmlMgmt->addSource($source);
        }
        $this->outputCss = null; // force output to be regenerated
        return $this;
    }

    /**
     * Add content that needs styling.
     *
     * @param string $content The content to search for selectable words (simplified selectors) in.
     * @return static
     */
    public function contentNeedsCss(string $content): self
    {
        $this->htmlMgmt->addSource(new HtmlString($content));
        $this->outputCss = null; // force output to be regenerated
        return $this;
    }

    /**
     * Manually specify selectors to always include.
     *
     * @param string|string[] $selectors Selectors to always add.
     * @return static
     */
    public function alwaysAddTheseSelectors($selectors): self
    {
        $this->cssMgmt->alwaysAddSelectors($selectors);
        $this->outputCss = null; // force output to be regenerated
        return $this;
    }

    /**
     * Specify whether unused css-definitions should be removed or not.
     *
     * @param boolean $removeUnused Should unused css-definitions be removed?.
     * @return static
     */
    public function removeUnused(bool $removeUnused = true): self
    {
        $this->removeUnused = $removeUnused;
        $this->outputCss = null; // force output to be regenerated
        return $this;
    }

    /**
     * Specify whether the output should be minified or not.
     *
     * @param boolean $minify Should the output be minified?.
     * @return static
     */
    public function minify(bool $minify = false): self
    {
        $this->minify = $minify;
        $this->outputCss = null; // force output to be regenerated
        return $this;
    }


    /**
     * Return the custom css.
     *
     * @param string $leadingWhitespace The whitespace to add to the beginning of each line.
     * @return string
     */
    public function render(string $leadingWhitespace = ''): string
    {
        if (!is_null($this->outputCss)) {
            return $this->outputCss;
        }

        // read the css-selectors from the specified Sources
        $this->cssMgmt->processCssDefinitions();

        // find the words (simplified selectors) inside the html content
        $detectedWords = ($this->removeUnused
            ? $this->htmlMgmt->findSelectableWords($this->cssMgmt->optionalWords())
            : []);

        // build the css ready for use
        $this->outputCss = $this->cssMgmt->buildCss(
            $detectedWords,
            $this->minify,
            $leadingWhitespace,
            !$this->removeUnused
        );

        // build if not built already
        return (string) $this->outputCss;
    }
}
