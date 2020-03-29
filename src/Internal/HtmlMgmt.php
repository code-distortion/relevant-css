<?php

namespace CodeDistortion\RelCss\Internal;

use CodeDistortion\RelCss\Internal\Html\HtmlInterface;

/**
 * Manage the html sources.
 */
class HtmlMgmt
{
    /**
     * The places to search for selectable words.
     *
     * @var HtmlInterface[]
     */
    protected $sources = [];

    /**
     * The words that were detected in the html.
     *
     * @var boolean[]
     */
    protected $detectedWords = [];





    /**
     * Add html to detect css usage in.
     *
     * @param HtmlInterface $source The source to add.
     *
     * @return static
     */
    public function addSource(HtmlInterface $source): self
    {
        $this->sources[] = $source;
        return $this;
    }





    /**
     * Find the selectable words (simplified selectors) in the html.
     *
     * @param boolean[] $possibleWords The list of possible words to choose from.
     * @return boolean[]
     */
    public function findSelectableWords(array $possibleWords): array
    {
        $this->detectedWords = [];
        foreach ($this->sources as $source) {
            $this->findSelectableWithin($source->html(), $possibleWords);
        }
        return $this->detectedWords;
    }

    /**
     * Find the words (simplified selectors) in the given file that might be selectable.
     *
     * @param string    $html          The html to look inside.
     * @param boolean[] $possibleWords The list of possible words to choose from.
     * @return void
     */
    protected function findSelectableWithin(string $html, array $possibleWords): void
    {
        // add these other words if they're present in the html
        $words = SelectorTools::extractSelectables($html);
        foreach (array_keys($possibleWords) as $word) {
            if (isset($words[$word])) {
                $this->detectedWords[$word] = true;
            }
        }
    }

    /**
     * Return the words (simplified selectors) that were detected.
     *
     * @return boolean[]
     */
    public function detectedWords()
    {
        return $this->detectedWords;
    }
}
