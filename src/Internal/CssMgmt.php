<?php

namespace CodeDistortion\RelCss\Internal;

use CodeDistortion\RelCss\Filesystem\FilesystemInterface;
use CodeDistortion\RelCss\Internal\Css\CssInterface;
use Sabberworm\CSS\CSSList\AtRuleBlockList;
use Sabberworm\CSS\OutputFormat;
use Sabberworm\CSS\Parser;
use Sabberworm\CSS\RuleSet\DeclarationBlock;
use Throwable;

/**
 * Manage the css sources.
 */
class CssMgmt
{
    use HasFilesystemTrait;


    /**
     * The directory to cache parsed css in.
     *
     * @var string
     */
    protected $cacheDir;

    /**
     * The sources to get css-definitions from.
     *
     * @var CssInterface[]
     */
    protected $sources = [];

    /**
     * Manually specified selectors to always use.
     *
     * @var boolean[]
     */
    protected $customCompulsoryWords = [];

    /**
     * When true, any changes to the contents of the source files will be detected.
     *
     * (this may run slightly slower).
     *
     * @var boolean
     */
    protected $autoReCache = true;

    /**
     * The css path hash once generated (to save it from needing to be generated a second time).
     *
     * @var string|null
     */
    protected $hash = null;


    /**
     * Selectors that were found in the source css.
     *
     * @var array[]
     */
    protected $selectors = [];

    /**
     * Styles that were found in the source css.
     *
     * @var array[]
     */
    protected $styles = [];

    /**
     * Mappings between words (simplified selectors) and their proper selectors / styles.
     *
     * @var array[]
     */
    protected $wordIndexes = [];


    /**
     * Words (simplified selectors) from the source css that might appear in the content files.
     *
     * @var boolean[]
     */
    protected $optionalWords = [];

    /**
     * Words (simplified selectors) from the source css that will always be added regardless.
     *
     * @var boolean[]
     */
    protected $compulsoryWords = [];


    /**
     * Constructor.
     *
     * @param FilesystemInterface $filesystem  Used to access the filesystem.
     * @param string              $cacheDir    The directory to cache parsed css in.
     * @param boolean             $autoReCache When true, any changes to the contents of the source files will be
     *                                         detected.
     */
    public function __construct(FilesystemInterface $filesystem, string $cacheDir = '', bool $autoReCache = true)
    {
        $this->filesystem = $filesystem;
        $this->cacheDir = rtrim($cacheDir, '/');
        $this->autoReCache = $autoReCache;
    }


    /**
     * Reset the things that this class determines by executing.
     *
     * @return void
     */
    protected function resetExtracted(): void
    {
        $this->selectors = $this->styles = $this->wordIndexes = [];
        $this->optionalWords = $this->compulsoryWords = [];
    }

    /**
     * Return the values to be cached.
     *
     * @return mixed[]
     */
    protected function buildValuesToCache(): array
    {
        return [
            'selectors' => $this->selectors,
            'styles' => $this->styles,
            'wordIndexes' => $this->wordIndexes,
            'optionalWords' => $this->optionalWords,
            'compulsoryWords' => $this->compulsoryWords,
        ];
    }

    /**
     * Take the cached values and use them.
     *
     * @param mixed[] $values The cached values to use.
     * @return void
     */
    protected function applyCachedValues(array $values): void
    {
        $this->selectors = $values['selectors'];
        $this->styles = $values['styles'];
        $this->wordIndexes = $values['wordIndexes'];
        $this->optionalWords = $values['optionalWords'];
        $this->compulsoryWords = $values['compulsoryWords'];
    }


    /**
     * Read previously extracted details from file.
     *
     * @return boolean
     */
    protected function loadCachedExtractedStyles(): bool
    {
        try {
            $cachePath = $this->buildCachePath();
            if (($cachePath) && ($this->filesystem->exists($cachePath))) {
                $values = $this->filesystem->getRequire($cachePath);
                $this->applyCachedValues($values);
                return true;
            }
        } catch (Throwable $e) {
        }
        return false;
    }

    /**
     * Write the extracted css details out to file for quick access later.
     *
     * @return boolean
     */
    protected function cacheExtractedStyles(): bool
    {
        $cachePath = $this->buildCachePath();
        if ($cachePath) {

            $content = "<?php\n"
                ."// RelevantCss cache file\n"
                ."return ".var_export($this->buildValuesToCache(), true).";\n";
            return $this->filesystem->put($cachePath, $content);
        }
        return false;
    }

    /**
     * Generate the path to cache the extracted styles in.
     *
     * @return string|null
     */
    protected function buildCachePath()
    {
        if ($this->cacheDir) {
            return $this->cacheDir.'/RelevantCss.'.$this->hash().'.cache.php';
        }
        return null;
    }

    /**
     * Build a hash that represents the css path files.
     *
     * @return string
     */
    protected function hash(): string
    {
        if (is_null($this->hash)) {
            $parts = [];
            foreach ($this->sources as $source) {
                $parts[] = $source->hash();
            }
            $this->hash = md5(serialize($parts));
        }
        return $this->hash;
    }


    /**
     * Return all the optional words (simplified selectors) (as an array indexed by words).
     *
     * @return boolean[]
     */
    public function optionalWords(): array
    {
        return $this->optionalWords;
    }


    /**
     * Add a css-definition Source.
     *
     * @param CssInterface $source The Source to add.
     *
     * @return static
     */
    public function addSource(CssInterface $source): self
    {
        $source->autoReCache($this->autoReCache);
        $this->sources[] = $source;
        $this->hash = null; // force the hash to regenerate
        return $this;
    }

    /**
     * Manually specify selectors to always include.
     *
     * @param string|string[] $selectors Selectors to always add.
     * @return static
     */
    public function alwaysAddSelectors($selectors): self
    {
        $selectors = (is_array($selectors) ? $selectors : [$selectors]);
        foreach ($selectors as $words) {
            // grab the selectors
            preg_match_all('#[a-z0-9_\-/.:]+#', mb_strtolower($words), $matches);
            foreach ($matches[0] as $word) {
                $word = SelectorTools::breakDownSelector($word);
                $this->customCompulsoryWords[$word] = true;
            }
        }
        return $this;
    }


    /**
     * Read the css-selectors from the specified sources, process them and store them internally.
     *
     * @return void
     */
    public function processCssDefinitions(): void
    {
        $this->resetExtracted();
        if (!$this->loadCachedExtractedStyles()) { // load from cache
            $this->extractAllDefinitions();        // find all the css-definitions
            $this->cacheExtractedStyles();         // store in the cache
        }
    }

    /**
     * Loop through the list of source css files and extract the css-definitions from them.
     *
     * @return void
     */
    protected function extractAllDefinitions(): void
    {
        foreach ($this->sources as $source) {
            $this->extractDefinitions($source->cssDefinitions());
        }
    }

    /**
     * Take the given path and grab the css-definitions from inside.
     *
     * @param string $cssDefinitions The content to get css-definitions from.
     * @return boolean
     */
    protected function extractDefinitions(string $cssDefinitions): bool
    {
        try {
            $cssParser = new Parser($cssDefinitions);
            $cssDocument = $cssParser->parse();
            $this->processNode($cssDocument);
            return true;
        } catch (Throwable $e) {
        }
        return false;
    }

    /**
     * Look for css-definitions within this css node.
     *
     * @param mixed  $node  The css node to process.
     * @param string $media The css media that this node is currently in.
     * @return void
     */
    protected function processNode($node, string $media = ''): void
    {
        if ($node instanceof DeclarationBlock) {
            $this->recordDefinition(
                $media,
                $this->buildNodeSelectors($node),
                $this->buildNodeStyles($node)
            );
        } else {
            // put the next set of css within a @media block
            if ($node instanceof AtRuleBlockList) {
                $media = '@'.$node->atRuleName().' '.$node->atRuleArgs();
            }
            foreach ($node->getContents() as $childNode) {
                $this->processNode($childNode, $media);
            }
        }
    }

    /**
     * Build the selectors as a string for the given node.
     *
     * @param DeclarationBlock $node The node to build selectors for.
     * @return string[]
     */
    protected function buildNodeSelectors(DeclarationBlock $node): array
    {
        return array_map('strval', $node->getSelectors());
    }

    /**
     * Build the styles as a string for the given node.
     *
     * @param DeclarationBlock $node The node to build styles for.
     * @return string
     */
    protected function buildNodeStyles(DeclarationBlock $node): string
    {
        // render the styles with no space after the ":"
        $outputFormat = new OutputFormat();
        $outputFormat->set('SpaceAfterRuleName', '');

        $styles = [];
        foreach ($node->getRulesAssoc() as $rule) {
            $styles[] = $rule->render($outputFormat);
        }
        return rtrim(implode('', $styles), ';');
    }

    /**
     * Record the given css-definition.
     *
     * @param string   $media     The css media that these selectors are currently in.
     * @param string[] $selectors The selectors part of the definition as an array.
     * @param string   $styles    The styles rendered as a string.
     * @return void
     */
    protected function recordDefinition(string $media, array $selectors, string $styles): void
    {
        if (mb_strlen($styles)) {

            $nextIndex = (isset($this->selectors[$media]) ? count($this->selectors[$media]) : 0);
            $words = [];
            foreach ($selectors as $selector) {

                $optional = false;
                $word = SelectorTools::breakDownSelector($selector, $optional);

                // store the word
                if ($optional) {
                    $this->optionalWords[$word] = true;
                } else {
                    $this->compulsoryWords[$word] = true;
                }

                $this->wordIndexes[$media][$word][] = $nextIndex;
                $words[$word][] = $selector;
            }

            $this->selectors[$media][] = $words;
            $this->styles[$media][] = '{'.$styles.'}';
        }
    }


    /**
     * Build the css ready for use.
     *
     * @param boolean[] $detectedWords     The words whose selectors need to be added.
     * @param boolean   $minify            Should the output be minified?.
     * @param string    $leadingWhitespace The whitespace to add to the beginning of each line.
     * @param boolean   $allCss            Should all css be used regardless of what's in the html?.
     * @return string
     */
    public function buildCss(
        array $detectedWords,
        bool $minify = false,
        string $leadingWhitespace = '',
        bool $allCss = false
    ): string {
        $usedWords = ($allCss ? $this->buildAllPossibleWords() : $this->buildDetectedWords($detectedWords));

        // build the list of relevant selectors and their styles
        $selectors = $styles = [];
        foreach (array_keys($this->selectors) as $media) {
            foreach (array_keys($usedWords) as $word) {

                if (isset($this->wordIndexes[$media][$word])) {
                    foreach ($this->wordIndexes[$media][$word] as $index) {
                        $selectors[$media][$index] = $this->selectors[$media][$index];
                        $styles[$media][$index] = $this->styles[$media][$index];
                    }
                }
            }
        }

        // put the selectors + styles in the correct order
        $perMedia = [];
        foreach (array_keys($selectors) as $media) {

            ksort($selectors[$media]);
            ksort($styles[$media]);

            foreach ($selectors[$media] as $index => $words) {
                $curSelectors = [];
                foreach ($words as $word => $possibleSelectors) {
                    if (isset($usedWords[$word])) {
                        foreach ($possibleSelectors as $possibleSelector) {
                            $curSelectors[] = $possibleSelector;
                        }
                    }
                }
                if (count($curSelectors)) {
                    $perMedia[$media][] = implode(',', $curSelectors).$styles[$media][$index];
                }
            }
        }

        // piece together the css-definitions
        $output = '';

        // minified
        if ($minify) {
            foreach (array_keys($perMedia) as $media) {
                $output .= ($media ? $media.'{' : '');
                $output .= implode($perMedia[$media]);
                $output .= ($media ? '}' : '');
            }
            return $leadingWhitespace.$output.PHP_EOL;
        }

        // non-minified
        foreach (array_keys($perMedia) as $media) {
            if ($media) {
                $output .= $leadingWhitespace.$media.'{'.PHP_EOL;
                $output .= $leadingWhitespace.'    '.implode(
                        PHP_EOL.$leadingWhitespace.'    ',
                        $perMedia[$media]
                    ).PHP_EOL;
                $output .= $leadingWhitespace.'}'.PHP_EOL;
            } else {
                $output .= $leadingWhitespace.implode(PHP_EOL.$leadingWhitespace, $perMedia[$media]).PHP_EOL;
            }
        }

        return $output;
    }

    /**
     * Build an array of all of the possible words (simplified selectors).
     *
     * @return boolean[]
     */
    protected function buildAllPossibleWords()
    {
        $usedWords = [];
        foreach (array_keys($this->wordIndexes) as $media) {

            $currentWords = array_keys($this->wordIndexes[$media]);
            $usedWords = array_merge(
                $usedWords,
                ArraySupport::buildArrayFill($currentWords, true)
            );
        }
        return $usedWords;
    }

    /**
     * Build an array of the detected words (simplified selectors).
     *
     * @param boolean[] $detectedWords The words whose selectors need to be added.
     * @return boolean[]
     */
    protected function buildDetectedWords(array $detectedWords)
    {
        return array_merge(
            $this->compulsoryWords,
            $this->customCompulsoryWords,
            $detectedWords
        );
    }
}
