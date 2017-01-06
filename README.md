![](before_after.gif)

## PHP Typeset

PHP Typeset is a port of **[Typeset](https://github.com/davidmerfield/Typeset)** for JavaScript to PHP. Typeset is an HTML pre-processor for web typography. It provides correct quotation substitution, small-caps conversion, hyphenation, basic ligatures, hanging-punctuation, space substitution, and more.

Powered by [phpQuery](https://github.com/electrolinux/phpquery), this port for PHP 5.4+ retains all features, excluding hyphenation, which should be left up to the browser due to performance issues (it's recommended that you use **[Hypher](https://github.com/bramstein/hypher)** by Bram Stein as an alternative) and optical margin alignment (David disabled this in the JS version due to accessibility issues). It introduces the following modules:

- Simple math conversion (multiplication, division, exponents) (disabled by default)
- Ordinal wrapping (1st, 2nd ...)
- Conversion of parenthesised marks to their proper equivalents (© ℗ ® ℠ ™)
- Basic symbol conversion (numero №, silcrow/section §, interrobang ‽) (disabled by default)

See inside the class for more information.

### Installation & Usage

You'll need Composer to get started with Typeset:

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

Additionally, when changes are made to the JS version of Typeset, they will be mirrored here, if possible. The following JS to-do items will also be incorporated here when implemented:

- [Dewidowing](https://github.com/davidmerfield/Typeset/issues/34), *may* be implemented before David implements it.
- Remove recursion from `nodes()`. I'm not incredibly familiar with DOM-traversal, and so I'll leave this up to David.

Lastly:

- There is no CLI access... yet.
- Tests are forthcoming. Please don't use in production (unless you trust me) until the tests are up.

### License

In keeping with the spirit of the original Typeset for JavaScript, PHP Typeset is also dedicated to the public domain and licensed under **[CC0](LICENSE.md)**.