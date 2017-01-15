<?php

namespace Typeset\Support;

class Str
{
    /**
     * All types of spaces; used in expressions.
     */
    const ALL_SPACES =
        '\x{00a0}|\x{1361}|\x{2000}|\x{2001}|\x{2002}|\x{2003}|\x{2004}|' .
        '\x{2005}|\x{2006}|\x{2007}|\x{2008}|\x{2009}|\x{200a}|\x{200b}|' .
        '\x{200c}|\x{200d}|\x{202f}|\x{205f}|\x{3000}';

    const ANY_LATIN_CHAR =
         "\x{00c0}|\x{00c1}|\x{00c2}|\x{00c3}|\x{00c4}|\x{00c5}|\x{00c6}|\x{00c7}|\x{00c8}|\x{00c9}|\x{00ca}|\x{00cb}|\x{00cc}|\x{00cd}|\x{00ce}|\x{00cf}|\x{00d0}|\x{00d1}|\x{00d2}|\x{00d3}|\x{00d4}|\x{00d5}|\x{00d6}|\x{00d8}|\x{00d9}|\x{00da}|\x{00db}|\x{00dc}|\x{00dd}|\x{00de}|\x{00df}|\x{00e0}|\x{00e1}|\x{00e2}|\x{00e3}|\x{00e4}|\x{00e5}|\x{00e6}|\x{00e7}|\x{00e8}|\x{00e9}|\x{00ea}|\x{00eb}|\x{00ec}|\x{00ed}|\x{00ee}|\x{00ef}|\x{00f0}|\x{00f1}|\x{00f2}|\x{00f3}|\x{00f4}|\x{00f5}|\x{00f6}|\x{00f8}|\x{00f9}|\x{00fa}|\x{00fb}|\x{00fc}|\x{00fd}|\x{00fe}|\x{00ff}|\x{0100}|\x{0101}|\x{0102}|\x{0103}|\x{0104}|\x{0105}|\x{0106}|\x{0107}|\x{0108}|\x{0109}|\x{010a}|\x{010b}|\x{010c}|\x{010d}|\x{010e}|\x{010f}|\x{0110}|\x{0111}|\x{0112}|\x{0113}|\x{0114}|\x{0115}|\x{0116}|\x{0117}|\x{0118}|\x{0119}|\x{011a}|\x{011b}|\x{011c}|\x{011d}|\x{011e}|\x{011f}|\x{0120}|\x{0121}|\x{0122}|\x{0123}|\x{0124}|\x{0125}|\x{0126}|\x{0127}|\x{0128}|\x{0129}|\x{012a}|\x{012b}|\x{012c}|\x{012d}|\x{012e}|\x{012f}|\x{0130}|\x{0131}|\x{0132}|\x{0133}|\x{0134}|\x{0135}|\x{0136}|\x{0137}|\x{0138}|\x{0139}|\x{013a}|\x{013b}|\x{013c}|\x{013d}|\x{013e}|\x{013f}|\x{0140}|\x{0141}|\x{0142}|\x{0143}|\x{0144}|\x{0145}|\x{0146}|\x{0147}|\x{0148}|\x{0149}|\x{014a}|\x{014b}|\x{014c}|\x{014d}|\x{014e}|\x{014f}|\x{0150}|\x{0151}|\x{0152}|\x{0153}|\x{0154}|\x{0155}|\x{0156}|\x{0157}|\x{0158}|\x{0159}|\x{015a}|\x{015b}|\x{015c}|\x{015d}|\x{015e}|\x{015f}|\x{0160}|\x{0161}|\x{0162}|\x{0163}|\x{0164}|\x{0165}|\x{0166}|\x{0167}|\x{0168}|\x{0169}|\x{016a}|\x{016b}|\x{016c}|\x{016d}|\x{016e}|\x{016f}|\x{0170}|\x{0171}|\x{0172}|\x{0173}|\x{0174}|\x{0175}|\x{0176}|\x{0177}|\x{0178}|\x{0179}|\x{017a}|\x{017b}|\x{017c}|\x{017d}|\x{017e}|\x{017f}";

    /**
     * List of characters used throughout Typeset
     * @var array
     */
    const ALIASES = [
        'bdquo' => '201E', // „
        'copy' => '00A9', // ©
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
        'nbhyphen' => '2011', // non-breaking hyphen
        'nbsp' => '00A0', // non-breaking space
        'nnbsp' => '202F', // narrow non-breaking space
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
        'zwnbsp' => 'FEFF', // zero-width non-breaking space
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

        return json_decode("\"\\u{$code}\"");
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
