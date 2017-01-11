<?php

namespace Typeset\Support;

class Str
{
    /**
     * Determine if a word is an acronym
     * @param $word
     */
    public static function isAcronym($word)
    {
        return (
            strlen($word) &&
            strlen(trim($word)) > 1 &&
            !preg_match('/^\d+$/', preg_replace('/[\.,-\/#!–$%°\^&\*;?:+′|@\[\]{}=\-_`~()]/', '', $word)) &&
            preg_replace('/[\.,-\/#!$%\^&\*;–?:+|@\[\]{}=\-_`~(′°)]/', '', $word) === $word &&
            strcmp(strtoupper($word), $word) === 0
        );
    }

    /**
     * Split cruft (unwanted characters or pieces) into an array.
     * @param $word
     */
    public static function splitCruft($word)
    {
        $ignore = array_merge(preg_split("//u", "{}()-‘’[]!#$*&;:,.“”″′‘’\"'°", -1, PREG_SPLIT_NO_EMPTY), ['&quot;', "'s", "’s", '&#39;s']);
        $encodedIgnore = $ignore;

        foreach ($encodedIgnore as $key => $value) {
            $encodedIgnore[$key] = htmlspecialchars($encodedIgnore[$key]);
        }

        $ignore = array_merge($ignore, $encodedIgnore);

        $trailing = '';
        $leading = '';

        for ($i = 0; $i < count($ignore); $i++) {
            $ignoreThis = $ignore[$i];
            $endOfWord = substr($word, -strlen($ignoreThis));

            if ($endOfWord === $ignoreThis) {
                $trailing = $ignoreThis . $trailing;
                $word = substr($word, 0, -strlen($ignoreThis));
                $i = 0;
                continue;
            }
        }

        for ($j = 0; $j < count($ignore); $j++) {
            $ignoreThis = $ignore[$j];
            $startOfWord = substr($word, 0, strlen($ignoreThis));

            if ($startOfWord === $ignoreThis) {
                $leading .= $ignoreThis;
                $word = substr($word, strlen($ignoreThis));
                $j = 0;
                continue;
            }
        }

        return [$leading, $word, $trailing];
    }

    /**
     * Obtain a unicode character by code.
     * @param $code
     */
    public static function uchr($code)
    {
        return html_entity_decode(preg_replace('/([\da-fA-F]{4})/', '&#x$1;', $code));
    }
}
