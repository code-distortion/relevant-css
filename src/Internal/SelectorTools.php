<?php

namespace CodeDistortion\RelCss\Internal;

/**
 * Provides some tools for RelCss to use.
 */
class SelectorTools
{
    /**
     * Take the given css-selector and break it down into a simple word that can be regexed for.
     *
     * @param string  $selector The css-selector.
     * @param boolean $optional Track if this selector is optional? (otherwise it's always used).
     * @return string
     */
    public static function breakDownSelector(string $selector, bool &$optional = null): string
    {
        // use the last part when selector depth is > 1
        $temp = explode(' ', trim(mb_strtolower($selector)));
        $word = (string) end($temp); // use the last section of the selector

        // use the class part of the selector (eg. when "img.thumbnail" take the "thumbnail" part)
        $temp = explode('.', $word);
        $word = (string) end($temp); // use the last section of the selector

        // start $check off
        $optional = !(in_array(mb_substr($word, 0, 1), [':', '*']));

        // strip off the "[..."
        $word = static::stripSquareBracket($word, $optional);

        // remove the "." if it's a class
        if (mb_substr($word, 0, 1) == '.') {
            $word = mb_substr($word, 1);
            $optional = true;
        }

        // strip anything after the ":"
        $word = static::stripAfterColon($word);

        // check if it starts with a ":"
        $pos = mb_strpos($word, ':');
        if ($pos === 0) {
            $optional = false;
        } else {
            $word = stripslashes($word);
        }

        return $word;
    }

    /**
     * strip off the "[" from the given word (simplified selector).
     *
     * @param string  $word     The word to treat.
     * @param boolean $optional Track if this selector is optional? (otherwise it's always used).
     * @return string
     */
    protected static function stripSquareBracket(string $word, bool &$optional): string
    {
        $pos = mb_strpos($word, '[');
        if ($pos !== false) {
            if ($pos == 0) {
                // eg. "[type=checkbox]" or "[hidden]"
                // grab the part before the "="
                if (preg_match('/\[([^=\]]+).*]/', $word, $matches)) {
                    $word = $matches[1];
                    $optional = true;
                }
            } else {
                // just grab what's before the "["
                $word = mb_substr($word, 0, $pos);
                $optional = true;
            }
        }
        return $word;
    }

    /**
     * Strip anything after the ":" from the given word (simplified selector).
     *
     * Will strip unless nothing would be left (eg. for ":after").
     *
     * @param string $word The word to treat.
     * @return string
     */
    protected static function stripAfterColon(string $word): string
    {
        $wordParts = explode('\\:', $word);
        $newWordParts = [];
        foreach ($wordParts as $wordPart) {

            $pos = mb_strpos($wordPart, ':');
            if ($pos === false) {
                $newWordParts[] = $wordPart;
            } else {
                $newWordParts[] = mb_substr($wordPart, 0, $pos);
                break;
            }
        }
        $newWord = implode('\\:', $newWordParts);

        return (mb_strlen($newWord) ? $newWord : $word);
    }





    /**
     * Find the words in the given file that might be selectable.
     *
     * @param string $content The content to look inside.
     * @return boolean[]
     */
    public static function extractSelectables(string $content): array
    {
        // get words including "/"
        preg_match_all('#[a-z0-9_\-/.:]+#', mb_strtolower($content), $matches1);
        // get words excluding "/" (which may include self-closing tags like "hr" in "<hr/>"
        preg_match_all('#[a-z0-9_\-.:]+#', mb_strtolower($content), $matches2);
        $words = array_unique(array_merge($matches1[0], $matches2[0]));
        return ArraySupport::buildArrayFill($words, true);
    }
}
