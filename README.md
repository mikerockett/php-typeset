![](before_after.gif)

## PHP Typeset

PHP Typeset is a port of **[Typeset](https://github.com/davidmerfield/Typeset)** for JavaScript to PHP. Typeset is an HTML pre-processor for web typography. It provides correct quotation substitution, small-caps conversion, hyphenation, basic ligatures, hanging-punctuation, space substitution, and more.

This port for PHP 5.4+ retains all features, excluding hyphenation, which should be left up to the browser due to performance issues (it's recommended that you use **[Hypher](https://github.com/bramstein/hypher)** by Bram Stein as an alternative) and optical margin alignment (David disabled this in the JS version due to accessibility issues). It also introduces simple math conversion (multiplication, division, exponents) and ordinal wrapping, and adds number wrapping to the small-caps conversion (module has been renamed for this purpose).

See inside the class for more information.

### Usage

This hasn't been uploaded to Packagist. For now, simply use this to import the package:

```php
require_once 'Typeset.php';
```

Create a new Typeset object. Note that `hanging_punctuation` and `capitals_numbers` is disabled by default, for performance reasons. You can also opt to ignore specific elements by means of a CSS selector.

```php
$typeset = new Typeset(); // or

$typeset = new Typeset([]); // to enable all features, or

$typeset = new Typeset([
    'disable' => ['hanging_punctuation'], // array to disable a module, or
    'ignore' => '.skip, #anything, .which-matches', // to ignore elements, or
    'capitals_numbers' => ['disable_numbers'], // disable numbers in the capitals_numbers module.
]);
```

(Optional) Rename the classes that Typeset gives to `span` elements:

```php
$typeset->classCapitals = 'small-caps'; // default: 'capitals'
$typeset->classNumber = 'numerics'; // default: 'number'
$typeset->classOrdinal = 'ord'; // default: 'ordinal'
```

And *GO!*

```
$html = $typeset->typeset($html);
```

### Some notes

**Note that this is currently under development, and is subject to change.** When changes are made to the JS version of Typeset, they will be mirrored here, if possible. The following JS to-do items will also be incorporated here when implemented:

- [Dewidowing](https://github.com/davidmerfield/Typeset/issues/34), *may* be implemented before David implements it.
- Remove recursion from `nodes()`. I'm not incredibly familiar with DOM-traversal, and so I'll leave this up to David.

Lastly:

- There is no CLI access... yet.
- Tests are forthcoming. Please don't use in production (unless you trust me) until the tests are up.

### License

In keeping with the spirit of the original Typeset for JavaScript, PHP Typeset is also dedicated to the public domain and licensed under **[CC0](LICENSE.md)**.