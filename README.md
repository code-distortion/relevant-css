# Relevant CSS

[![Latest Version on Packagist](https://img.shields.io/packagist/v/code-distortion/relevant-css.svg?style=flat-square)](https://packagist.org/packages/code-distortion/relevant-css)
![PHP from Packagist](https://img.shields.io/packagist/php-v/code-distortion/relevant-css?style=flat-square)
[![GitHub Workflow Status](https://img.shields.io/github/workflow/status/code-distortion/relevant-css/run-tests?label=tests&style=flat-square)](https://github.com/code-distortion/relevant-css/actions)
[![Buy us a tree](https://img.shields.io/badge/treeware-%F0%9F%8C%B3-lightgreen?style=flat-square)](https://offset.earth/treeware?gift-trees)
[![Contributor Covenant](https://img.shields.io/badge/contributor%20covenant-v2.0%20adopted-ff69b4.svg?style=flat-square)](CODE_OF_CONDUCT.md)

***code-distortion/relevant-css*** is a PHP library that parses your css, analyses your html, and builds custom css with only the necessary definitions.

If you'd like to use this in [Laravel](https://packagist.org/packages/laravel/laravel), have a look at [code-distortion/laravel-relevant-css](https://packagist.org/packages/code-distortion/laravel-relevant-css) which integrates this package neatly into Laravel and the Blade templating system.

This package was inspired by [PurgeCss](https://purgecss.com/).

``` php
use CodeDistortion\RelCss\RelevantCss;

$outputCss = RelevantCss::new()
  ->cssFile('my.css')       // specify a source css file
  ->fileNeedsCss('my.html') // an html file that needs custom styles
  ->output();               // generate the custom css

print $outputCss;

/*
html{line-height:1.15;-webkit-text-size-adjust:100%}
body{margin:0}
input{font-family:inherit;font-size:100%;line-height:1.15;margin:0}
… etc
*/
```

## Installation

Install the package via composer:

``` bash
composer require code-distortion/relevant-css
```

## Usage

### Instantiation

``` php
use CodeDistortion\RelCss\RelevantCss;

$relevantCss = new RelevantCss();
// or
$relevantCss = RelevantCss::new(); // this is neater to chain with
```

### CSS input

First specify the css definitions that can be used. More than one source can be specified.

You can specify css files:

``` php
$relevantCss->cssFile('my.css');
```

or pass definitions directly as a string:

``` php
$cssDefinitions = <<<CSS
html { padding: 0; color: black; }
input { border: 1px solid grey; background-color: white; }
CSS;

$relevantCss->cssDefinitions($cssDefinitions);
```

### HTML to style

Then specify the html that needs styling. More than one can be specified.

You can specify html files:

``` php
$relevantCss->fileNeedsCss('my.html');
```

Or pass the html directly as a string:

``` php
$relevantCss->contentNeedsCss('<input type="text"/>');
```

### Additional selectors

You may also specify css selectors to always include, regardless of whether they appear in the html or not:

``` php
$relevantCss->alwaysAddSelectors('.success .warning .error');
```

### Output

Then generate the custom css as a string ready to use:

``` php
$outputCss = $relevantCss->output();
```

### Caching

It takes time to parse the css input and so to help improve this, RelevantCss will cache the css when you specify a cache-directory:

``` php
$relevantCss = RelevantCss::new('path/to/cache/dir');
```

### Filesystem

By default RelevantCSS will access the filesystem directly when loading files and storing cache data. However you can change how the filesystem is accessed by passing a filesystem object when instantiating it. It must implement `\CodeDistortion\RelCss\Filesystem\FilesystemInterface`. 

``` php
// DirectFilesystem is the filesystem used by default
use CodeDistortion\RelCss\Filesystem\DirectFilesystem;

$relevantCss = RelevantCss::new('path/to/cache/dir', new DirectFilesystem());
```

## Testing

``` bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

### SemVer

This library uses [SemVer 2.0.0](https://semver.org/) versioning. This means that changes to `X` indicate a breaking change: `0.0.X`, `0.X.y`, `X.y.z`. When this library changes to version 1.0.0, 2.0.0 and so forth it doesn't indicate that it's necessarily a notable release, it simply indicates that the changes were breaking.

## Treeware

You're free to use this package, but if it makes it to your production environment please plant or buy a tree for the world.

It's now common knowledge that one of the best tools to tackle the climate crisis and keep our temperatures from rising above 1.5C is to <a href="https://www.bbc.co.uk/news/science-environment-48870920">plant trees</a>. If you support this package and contribute to the Treeware forest you'll be creating employment for local families and restoring wildlife habitats.

You can buy trees here [offset.earth/treeware](https://offset.earth/treeware?gift-trees)

Read more about Treeware at [treeware.earth](http://treeware.earth)

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

### Code of conduct

Please see [CODE_OF_CONDUCT](CODE_OF_CONDUCT.md) for details.

### Security

If you discover any security related issues, please email tim@code-distortion.net instead of using the issue tracker.

## Credits

- [Tim Chandler](https://github.com/code-distortion)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
