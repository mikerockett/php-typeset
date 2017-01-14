<?php

namespace Typeset\Support;

class Str
{
    /**
     * List of characters used throughout Typeset
     * @var array
     */
    const ALIASES = [
        'bdquo' => '201E', // „
        'copy' => '24B8', // ©
        'divide' => '00F7', // ÷
        'dprime' => '2033', // ″
        'ellipses' => '2026', // …
        'emdash' => '2014', // —
        'endash' => '2013', // –
        'hairspace' => '200A',
        'interrobang' => '203D', // ‽
        'ldquo' => '201C', // “
        'lsquo' => '2018', // ‘
        'multiply' => '00D7', // ×
        'nbsp' => '00A0', // non-breaking space
        'numero' => '2116', // №
        'prime' => '2032', // ′
        'quot' => '0022', // "
        'rdquo' => '201D', // ”
        'reg' => '00AE', // ®
        'rsquo' => '2019', // ’
        'scopy' => '2117', // ℗
        'shyphen' => '00AD', // soft hyphen
        'silcrow' => '00A7', // §
        'smark' => '2120', // SM
        'thinspace' => '2009',
        'tmark' => '2122', // ™
        'tprime' => '2034', // ‴
        'zwsp' => '200B', // zero-width space
    ];

    /**
     * Obtain a unicode character by code.
     * @param $code
     */
    public static function uchr($code)
    {
        if (isset(self::ALIASES[$code])) {
            $code = self::ALIASES[$code];
        }

        return html_entity_decode(preg_replace('/([\da-fA-F]{4})/', '&#x$1;', $code));
    }

    /**
     * Obtain a sequence of unicode characters by their codes.
     * @param $codes
     */
    public static function uchrs($codes)
    {
        $result = '';
        foreach ($codes as $code) {
            $result .= self::uchr($code);
        }

        return $result;
    }
}
