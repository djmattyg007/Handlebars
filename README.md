Handlebars for PHP
====

 - [Install](#install)
 - [Introduction](#intro)
 - [Usage](#usage)
 - [Features](#features)
 - [Production Ready](#production)
 - [API](#api)
    - [compile](#compile)
    - [registerHelper](#registerHelper)
    - [registerPartial](#registerPartial)
    - [setCachePath](#setCachePath)
    - [setNamePrefix](#setCacheFilePrefix)
 - [Contributing](#contributing)

====

<a name="install"></a>
## Install

`composer require djmattyg007/handlebars`

====

<a name="intro"></a>
## Introduction

This is a PHP implementation of Handlebars templates that aims to match the interface of the JS
implementation. It does not provide some of the niceties found in other similar PHP libraries,
such as loading templates from files.

This is a fork of the Handlebars package from the Eden framework. It has been refactored to not
rely on the magic provided by the Core package from Eden, and to not treat most of the classes as
singletons. It also takes advantage of scalar type declarations available in PHP 7.

====

<a name="usage"></a>
## Basic Usage

#### Instantiating the Handlebars class

There are several parts to the Handlebars object. The first is the Runtime object. The Runtime is
what ends up containing all helpers and partials. It is passed to Templates when they are compiled,
and also the Compiler for use during compilation.

```php
use MattyG\Handlebars\Runtime;
$runtime = new Runtime();
// Pass false to the constructor to have it not load the default Helpers
$helperlessRuntime = new Runtime(false);
```

The Compiler has two dependencies: the Runtime, and a factory to produce Tokenizers. Each time a
new Template is compiled, a new Tokenizer is created using the Tokernizer factory.

```php
use MattyG\Handlebars\Compiler;
use MattyG\Handlebars\TokenizerFactory;
$compiler = new Compiler($runtime, new TokenizerFactory());
```

Finally, we instanstiate the actual Handlebars object. This is the object you'll be interacting
with to register helpers and partials, and to compile templates. It has three dependencies: the
Runtime, the Compiler, and a factory to produce Data objects. Data objects are used to help easily
navigate the context provided when rendering a Template.

```php
use MattyG\Handlebars\DataFactory;
use MattyG\Handlebars\Handlebars;
$handlebars = new Handlebars($runtime, $compiler, new DataFactory());
```

It is recommended that you use a dependency injection system to handle construction of the main
Handlebars object, so that the only part you need to worry about is registering your helpers and
partials, and actually compiling your templates.

#### Rendering 

```php
$template = $handlebars->compile('{{foo}} {{bar}}');

$content = $template->render(array('foo' => BAR, 'bar' =. 'ZOO'));
// Or used as a callable
$content = $template(array('foo' => 'BAZ', 'bar' => 'ABC'));
```

#### Registering Helpers

```php
$handlebars->registerHelper('bar', function($options) {
    return 'BAZ';
});
$template = $handlebars->compile('{{foo}} {{bar}}');
echo $template(array('foo' => 'BAR'));
```

#### Registering Partials

```php
$handlebars->registerPartial('bar' => 'ABC');
$template = $handlebars->compile('{{foo}} {{> bar}}');
echo $template->render(array('foo' => 'BAR'));
```

====

<a name="features"></a>
## Features

 - PHP API - designed to match the handlebars.js documentation
     - registerHelper() - matches exactly what you expect from handlebars.js (except it's PHP syntax)
     - registerPartial() - accepts strings and functions as callbacks
     - Literals like `{{./foo}}` and `{{../bar}}` are evaluated properly
     - Comments like `{{!-- Something --}}` and `{{! Something }}` supported
     - Trims like `{{~#each}}` and `{{~foo~}}` supported
     - Mustache backwards compatibility `{{#foo}}{{this}}{{/foo}}`
 - Default Helpers matching handlebars.js
     - if
     - each - and `{{#each foo as |value, key|}}`
     - unless
     - with

<a name="features-todo"></a>
### Features on the todo list

 - Safe String/Escaping

====

<a name="production"></a>
## Production Ready

When your templates are ready for a production (live) environment, it is recommended that caching be used. To enable cache:

 - Create a cache folder and make sure permissions are properly set for handlebars to write files to it.
 - Enable cache by using `$handlebars->setCachePath(__DIR__ . '/your/cache/folder/location');`
 - The code will not attempt to create the specified folder if it doesn't exist.

====

<a name="api"></a>
## API

==== 

<a name="compile"></a>

### compile

Returns a callback that binds the data with the template 

#### Usage

```php
$template = $handlebars->compile(string $string);
```

#### Parameters

 - `string $string` - the template string

Returns an instance of `MattyG\Handlebars\Template` - call render() on the template object (or use
it as a callable) to render it.

#### Example

```php
$template = $handlebars->compile('{{foo}} {{bar}}');
echo $template->render(array('foo' => 'FOO', 'bar' => 'BAR'));
// result: 'FOO BAR'
```

==== 

<a name="registerHelper"></a>

### registerHelper

Register a helper to be used within templates.

Note that unlike the JS implementation of Handlebars, the concept of "this" is not bound to the
current context inside of a helper. This is actually a freedom: it allows you to use any type of
callable you wish.

#### Usage

```php
$handlebars->registerHelper(string $name, callable $helper);
```

#### Parameters

 - `string $name` - the name of the helper
 - `callable $helper` - the helper handler

#### Example

```php
$handlebars->registerHelper('baz', function() { return 'BAZ' });
$template = $handlebars->compile('{{foo}} {{baz}}');
echo $template(array('foo' => 'FOO'));
// result: 'FOO BAZ'
```

==== 

<a name="registerPartial"></a>

### registerPartial

Registers a reusable partial template for use within other templates

#### Usage

```php
$handlebars->registerPartial(string $name, string $partial);
```

#### Parameters

 - `string $name` - the name of the partial
 - `string $partial` - the template string of the partial

#### Example

```php
$handlebars->registerPartial('zoo', '1 + 2');
$template = $handlebars->compile('{{> zoo}} = {{result}}');
echo $template->render(array('result' => 3));
// result: '1 + 2 = 3'
```

==== 

<a name="setCachePath"></a>

### setCachePath

Enables caching of compiled templates.

#### Usage

```php
$handlebars->setCachePath(string $cachePath);
```

#### Parameters

 - `string $cachePath` - The path to store cached copies of compiled templates

#### Example

```php
$handlebars->setCachePath('/path/to/cache/folder');
```

====  

<a name="setNamePrefix"></a>

### setNamePrefix

Sets the name prefix for compiled templates. This is used to avoid conflicts when generating
templates.

#### Usage

```php
$handlebars->setNamePrefix(string $namePrefix);
```

#### Parameters

 - `string $namePrefix` - Custom prefix name

#### Example

```
$handlebars->setNamePrefix('special-template-');
```

==== 

<a name="contributing"></a>

#Contributing

All contributions are welcome. Ideally, all code contributions will come with new or updated tests :)
