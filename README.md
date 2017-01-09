![](before_after.gif)

## PHP Typeset

PHP Typeset is a port of **[Typeset](https://github.com/davidmerfield/Typeset)** for JavaScript to PHP. Typeset is an HTML pre-processor for web typography. It provides correct quotation substitution, small-caps conversion, hyphenation, basic ligatures, hanging-punctuation, space substitution, and more.

Powered by [phpQuery](https://github.com/electrolinux/phpquery), this port for PHP 5.4+ retains all features, excluding hyphenation, which should be left up to the browser due to performance issues (it’s recommended that you use **[Hypher](https://github.com/bramstein/hypher)** by Bram Stein as an alternative) and optical margin alignment (David disabled this in the JS version due to accessibility issues). It introduces the following modules:

- Simple math conversion (multiplication, division, exponents) (disabled by default)
- Ordinal wrapping (1st, 2nd ...)
- Conversion of parenthesised marks to their proper equivalents (© ℗ ® ℠ ™)
- Basic symbol conversion (numero №, silcrow/section §, interrobang ‽) (disabled by default)

See inside the class for more information.

### Installation & Usage

You’ll need Composer to get started with Typeset:

Run `composer require rockett/php-typeset:dev-master` (no version-release as yet) or download the library and run `composer install` to get the phpQuery dependency.

```php
// Require the autoloader:
require_once __DIR__ . 'vendor/autoload.php';

// Use the class (or use the full class reference when creating an instance):
use Typeset\Typeset;
```

**Create a new Typeset instance:**

> Note that, for performance reasons, the `HangingPunctuation` and `SmallCaps` modules are disabled by default, and the `SimpleMath` module is disabled as it is experimental. `Ligatures` is also disabled as browsers now do this for us (see the deprecation notice in the module class file). You can, however, enable it if you wish. You can also opt to ignore specific elements by means of a CSS selector, and, where available, disable certain aspects of specific modules.

```php
$typeset = new Typeset(); // or

$typeset = new Typeset([
	// Enable all modules
	'disable' => [],
]); // or

$typeset = new Typeset([

	// Disable a module; overrides the default:
    'disable' => ['HangingPunctuation'],

    // Don't allow Typeset to process any of these:
    'ignore' => '.skip, #anything, .which-matches',

    // Turn off specific symbol conversions
    'symbols' => [
    	'disable' => ['numero', 'interrobang', 'silcrow'],
    ],

    // Rename the classes that certain modules use for span elements
    'ordinals' => [
        'class' => 'ordinal',
    ],
    'smallCaps' => [
        'class' => 'small-caps',
    ],
    'simpleMath' => [
        'exponentClass' => 'exponent',
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
- See what can be done to improve performance (speed is the current issue). At this point, `preg_replace` seems to be a culprit in a few modules. Perhaps we could use some sort of `str_replace` trickery or perhaps a string tokeniser (not familiar with such; just sprung to mind). [Refer: #1]
- Check order of modules — seems to be okay in current form. Some modules *could* be merged with others and renamed accordingly. Then certain parts of these merged modules could be disabled by config.
- Tests! Please don’t use in production (unless you trust me) until the tests are up. Once they are up, I’ll start a semver release pattern.

### License

In keeping with the spirit of the original Typeset for JavaScript, PHP Typeset is also dedicated to the public domain and licensed under **[CC0](LICENSE.md)**.