> ### Project Retired
> PHP Typeset is ‘retired’, as of 26 September 2017. An excellent alternative is [PHP Typography](https://github.com/mundschenk-at/php-typography), now installable via Composer.

![](before_after.gif)

**[PHP Typeset](https://rockett.pw/typeset/)** is a port of [Typeset.js](https://github.com/davidmerfield/Typeset) to PHP. Typeset is an HTML pre-processor for web typography. It provides correct quotation substitution, small-caps conversion, hyphenation, basic ligatures, hanging-punctuation, space substitution, and more.

Powered by [phpQuery](https://github.com/electrolinux/phpquery), this port for PHP 5.4+ retains all features, excluding hyphenation, which should be left up to the browser due to performance issues (it’s recommended that you use **[Hypher](https://github.com/bramstein/hypher)** by Bram Stein as an alternative) and optical margin alignment (David disabled this in the JS version due to accessibility issues). It changes certain implementations, and introduces the following modules:

- Simple math conversion (multiplication, division, exponents) (disabled by default)
- Ordinal wrapping (1st, 2nd ...)
- Conversion of parenthesised marks to their proper equivalents (© ℗ ® ℠ ™)
- Basic symbol conversion (numero №, silcrow/section §, interrobang ‽) (disabled by default)

**This is the complete list of available modules:**

Name | Description | Default
---|---|---
Quotes | Handles correct replacement of straight-quotation-marks, converting them to their correct, contextual equivalents. Processes for double quotes, single quotes, and then those that remain are converted to either single or double primes. Allows for straight quotes to be escaped for preservation. | On
Marks | Converts parenthesised marks to their proper equivalents. Processes for copyright, sound-recording copyright, registered trademark, serice mark, and trademark symbols. | On
Symbols | Symbol conversion. Processes for interrobangs, numeros, and silcrows). | Off
SmallCaps | Wraps abbreviations and acronyms in `span` elements for CSS styling. | Off
Punctuation | Convert hypens and double hyphens to dashes, and triple-periods to ellipses. Insert a non-breaking-space before and after specific punctuation marks. This module has similarities to the Symbols module, and may be merged in the future. | On
HangingPunctuation | Wrap hanging punctuation in `span` elements for CSS styling. Processes for single and double quotation-marks. | Off
SimpleMath | Experimental: Very simple equation formatters. | Off
Ordinals | Wrap ordinal suffixes in `sup` elements. | On
Spaces | Use thin spaces around division and multiplication signs and forward slashes. | On
Ligatures | Convert common ligatures in the case that a font does not display them normally. **Deprecation Notice:** This module will be removed in a future release. Browsers support ligatures in a proper manner. These ligatures are not available in all fonts, and so usage of this module is discouraged. | Off

### Installation & Usage

You’ll need Composer to get started with Typeset:

Run `composer require rockett/php-typeset:dev-master` (no version-release as yet) or download the library and run `composer install` to get the phpQuery dependency.

```php
// Require the autoloader:
require_once __DIR__ . 'vendor/autoload.php';

// Use the class (or use the full class reference when creating an instance):
use Typeset\Typeset;
```

**Create a new Typeset instance:** Optionally, you can pass a configuration array to the constructor, which uses an explicit opt-in and override pattern. This means that Typeset will use the modules enabled by default unless you specify a `modules` key with the modules you wish to enable.

```php
// Set defaults
$typeset = new Typeset(); // or
```

```php
// Only enable specific modules
$typeset = new Typeset([
	'modules' => ['Quotes', 'Punctuation', 'HangingPunctuation'],
]); // or
```

```php
// Enable all modules
$typeset = new Typeset([
    'modules' => Typeset::MODULES,
]);
```

Alternatiely, you can enable and disable modules using the `enable($module)` and `disable($module)` methods, passing the name of the module as the argument. You can also pass an array to either of these methods to toggle multiple modules at once. Both methods will gracefully ignore modules that do not require a state-change (already disabled/enabled).

```php
$typeset->disable('HangingPunctuation');
$typeset->enable('Symbols');
$typeset->enable(['Marks', 'Ordinals']);
```

In terms of options, these will not change unless they are directly overriden.

```php
// Options:
$typeset = new Typeset([

    // Custom properties - more may be added in the future
    'properties' => [
        // Use custom HTML5 elements instead of span elements.
        // Ex: <span class="small-caps"></span>
        // ->  <small-caps></small-caps>
        //
        // This property is set to 'span' by default.
        // You can use any tag-name you like, but ensure
        // it contains a hyphen, in terms of the spec.
        // Ex: 'ts-wrap'
        //
        // When blank, Typeset uses the class name as
        // the element name instead of adding the class
        // attribute to the element.
        'spanElement' => '',
    ],

    // Don't allow Typeset to process any of these:
    'ignore' => '.skip, #anything, .which-matches',

    // Turn off specific symbol conversions
    'symbols' => [
    	'disable' => ['numero', 'interrobang', 'silcrow'],
    ],

    // Rename the classes that certain modules use for span elements.
    // NOTE: If properties->spanElement is blank, a hyphen (-) MUST appear
    //       in the class name below, according to the HTML5 spec.
    'ordinals' => [
        'class' => 'ordinal-suffix', // default = ordinal
    ],
    'smallCaps' => [
        'class' => 'acronym', // default = small-caps
    ],
    'simpleMath' => [
        'exponentClass' => 'exp', // default = exponent
    ],

    // Other module-specific properties
    'quotes' => [
        'primes' => true, // turn this off if a font does not include primes
    ]
    'punctuation' => [
        // Use these defaults or select your own.
        // * Note that if you turn off phoneNumbers (this feature only applies
        // to hyphenated phone numbers) and leave numericRanges on,
        // such phone numbers may be treated as numerical ranges. It's recommended
        // to leave both features on.
        'features' => [
            'dashes',
            'numericRanges',
            'periodsEllipses',
            'phoneNumbers'
        ],
        // Set to a hairspace or thinspace,
        // or blank/null to turn off.
        'parentheticalDashWrapper' => 'hairspace',
    ]

]);
```

And *GO!*

```
$html = $typeset->typeset($html);
```

### Some notes

**The following features/enhancements are not available:**

- [Dewidowing](https://github.com/davidmerfield/Typeset/issues/34)
- Explore the possibility of switching to an HTML5-compatible parser.
- See what can be done to improve performance
- Other related modules, such as hyphenation


### License

In keeping with the spirit of the original Typeset for JavaScript, PHP Typeset is also dedicated to the public domain and licensed under **[CC0](LICENSE.md)**.
