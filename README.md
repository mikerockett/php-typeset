![](before_after.gif)

**PHP Typeset** is a port of **[Typeset.js](https://github.com/davidmerfield/Typeset)** to PHP. Typeset is an HTML pre-processor for web typography. It provides correct quotation substitution, small-caps conversion, hyphenation, basic ligatures, hanging-punctuation, space substitution, and more.

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

]);
```

And *GO!*

```
$html = $typeset->typeset($html);
```

### Some notes

**In ALPHA, PHP Typeset is currently under development, and is subject to change.**

On the to-do list:

- [Dewidowing](https://github.com/davidmerfield/Typeset/issues/34)
- Explore the possibility of switching to an HTML5-compatible parser. At this time, only [one](https://github.com/Masterminds/html5-php) appears to be worthy. This would require a change in architecture as the process for replacing nodes/data thereof would change.
- See what can be done to improve performance (speed is the current issue). At this point, `preg_replace` seems to be a culprit in a few modules. Perhaps we could use some sort of `str_replace` trickery or perhaps a string tokeniser (not familiar with such; just sprung to mind). (Refer: [#1](https://github.com/mikerockett/php-typeset/issues/1))
  - 2017-01-14: Performance for the SmallCaps module has been improved significantly as it now uses the [same method](https://github.com/mundschenk-at/wp-typography/blob/master/php-typography/class-settings.php#L684) found in [PHP Typography](https://github.com/mundschenk-at/wp-typography/tree/master/php-typography) (GNU GPL). Several different methods were attempted, and this method landed up winning. The original method by David *may* have been faster on JS (untested), but 200-400ms on PHP is far too long.
- Check order of modules — seems to be okay in current form. Some modules *could* be merged with others and renamed accordingly. Then certain parts of these merged modules could be disabled by config.
  - 2017-01-14: Module orders have been modified to prevent whitespace bugs relating to en/em dashes. being discarded when splitting paragraphs into arrays.
- Tests! Please don’t use in production (unless you trust me) until the tests are up. Once they are up, I’ll start a semver release pattern.

**phpQuery** has now been included in the library itself due to several changes that needed to be made to allow for custom elements. Additionally, phpQuery has been trimmed down considerably, where unneeded features were removed. With that said, it is still essential that Typeset adopts an HTML5 parser for the future.

### License

In keeping with the spirit of the original Typeset for JavaScript, PHP Typeset is also dedicated to the public domain and licensed under **[CC0](LICENSE.md)**.