![logo](http://eden.openovate.com/assets/images/cloud-social.png) Eden Handlebars
====
[![Build Status](https://api.travis-ci.org/Eden-PHP/Handlebars.png)](https://travis-ci.org/Eden-PHP/Handlebars)
====

 - [Install](#install)
 - [Introduction](#intro)
 - [Usage](#usage)
 - [Features](#features)
 - [De-Features](#defeatures)
 - [Production Ready](#production)
 - [API](#api)
    - [compile](#compile)
    - [getCachePath](#getCachePath)
    - [getHelper](#getHelper)
    - [getHelpers](#getHelpers)
    - [getPartial](#getPartial)
    - [getPartials](#getPartials)
    - [registerHelper](#registerHelper)
    - [registerPartial](#registerPartial)
    - [setCacheFilePrefix](#setCacheFilePrefix)
    - [setCachePath](#setCachePath)
    - [unregisterHelper](#unregisterHelper)
    - [unregisterPartial](#unregisterPartial)
 - [Contributing](#contributing)

====

<a name="install"></a>
## Install

`composer install eden/handlebars`

====

## Enable Eden

The following documentation uses `eden()` in its example reference. Enabling this function requires an extra step as descirbed in this section which is not required if you access this package using the following.

```
Eden\Handlebars\Index::i();
```

When using composer, there is not an easy way to access functions from packages. As a workaround, adding this constant in your code will allow `eden()` to be available after. 

```
Eden::DECORATOR;
```

For example:

```
Eden::DECORATOR;

eden()->inspect('Hello World');
```

====

<a name="intro"></a>
## Introduction

PHP Handlebars with JS interface to match with compile time helper support and super nice compile 
time error reporting. This version of Handlebars is based on caching the compiled templates and 
inheritently made the overall compile times faster. Loading at ~50ms uncached and ~30ms cached. 

====

<a name="usage"></a>
## Basic Usage

#### Rendering 

```
$template = eden('handlebars')->compile('{{foo}} {{bar}}');

echo $template(array('foo' => 'BAR', 'bar' => 'ZOO'));
```
#### Registering Helpers 

```
$template = eden('handlebars')
	->registerHelper('bar', function($options) {
		return 'ZOO';
	})
	->compile('{{foo}} {{bar}}');

echo $template(array('foo' => 'BAR'));
```

#### Registering Partials

```
$template = eden('handlebars')
	->registerPartial('bar', 'zoo')
	->compile('{{foo}} {{> bar}}');

echo $template(array('foo' => 'BAR'));
```

====

<a name="features"></a>
## Features

 - PHP API - designed to match the handlebars.js documentation
     - registerHelper() - Matches exactly what you expect from handlebars.js (except it's PHP syntax)
     - registerPartial() - accepts strings and functions as callbacks
     - Literals like `{{./foo}}` and `{{../bar}}` are evaluated properly
     - Comments like `{{!-- Something --}}` and `{{! Something }}` supported
	 - Trims like `{{~#each}}` and `{{~foo~}}` supported
	 - Mustache backwards compatibility `{{#foo}}{{this}}{{/foo}}`
	 - Tokenizer helpers to optimize custom code generation to cache
	 - Event handlers for unknown helpers and unknown partials
 - Default Helpers matching handlebars.js
     - each - and `{{#each foo as |value, key|}}`
	     - Please note that there is an issue with `each` being slow depending on the size of the object
		 - We need help optimizing this
	 - with
	 - unless
	 - if 

<a name="defeatures"></a>
## De-Features (or whatever the opposite of features is)

 - Does not support file templates. 
     - You need to load them up and pass it into Handlebars. 
     - If this is a problem you should consider other Handlebars PHP libraries
	 - You can always create a helper for this
	 - This de-feature will be considered upon requests ( create an issue :) )
 - Partial Failover
     - Something we haven't had a chance to come around doing yet as we did not have a need
	 - This de-feature will be considered upon requests ( create an issue :) )
 - Safe String/Escaping
     - PHP has functions that can turn a string "safe". 
	 - We didn't want to create something that already exists in other contexts
	 - This de-feature will be considered upon requests ( create an issue :) )
 - Utils
     - PHP has functions that support most of the listed Utils in handlebars.js 
	 - We didn't want to create something that already exists in other contexts
	 - This de-feature will be considered upon requests ( create an issue :) )
 - Dynamic Partials
     - At the bottom of our pipe 
	 - because of it's difficulty to recreate
	 - and practicality
	 - This de-feature will be considered upon requests ( create an issue :( )
 - Inline Partials
     - TODO
 - Decorators
 	 - TODO
 - Frames
 	 - TODO

====

<a name="production"></a>
## Production Ready

When your templates are ready for a production (live) environment, it is recommended that caching be used. To enable cache:

 - Create a cache folder and make sure permissions are properly set for handlebars to write files to it.
 - Enable cache by using `eden('handlebars')->setCachePath(__DIR__.'/your/cache/folder/location');`
 - If the folder location does not exist, caching will be disabled.

====

<a name="api"></a>
## API

==== 

<a name="compile"></a>

### compile

Returns a callback that binds the data with the template 

#### Usage

```
eden('handlebars')->compile(string $string);
```

#### Parameters

 - `string $string` - the template string

Returns `function` - the template binding handler

#### Example

```
eden('handlebars')->compile();
```

====

<a name="getCachePath"></a>

### getCachePath

Returns the active cache path

#### Usage

```
eden('handlebars')->getCachePath();
```

Returns `Closure`

==== 

<a name="getHelper"></a>

### getHelper

Returns a helper given the name

#### Usage

```
eden('handlebars')->getHelper('if');
```

#### Parameters

- `string $name` - the name of the helper

Returns `Closure`

==== 

<a name="getHelpers"></a>

### getHelpers

Returns all the registered helpers 

#### Usage

```
eden('handlebars')->getHelpers();
```

#### Parameters

Returns `array`

====

<a name="getPartial"></a>

### getPartial

Returns a partial given the name

#### Usage

```
eden('handlebars')->getPartial('foobar');
```

#### Parameters

- `string $name` - the name of the partial

Returns `string`

==== 

<a name="getPartials"></a>

### getPartials

Returns all the registered partials 

#### Usage

```
eden('handlebars')->getPartials();
```

#### Parameters

Returns `array`

==== 

<a name="registerHelper"></a>

### registerHelper

The famous register helper matching the Handlebars API 

#### Usage

```
eden('handlebars')->registerHelper(string $name, function $helper);
```

#### Parameters

 - `string $name` - the name of the helper
 - `function $helper` - the helper handler

Returns `Eden\Handlebrs\Index`

#### Example

```
eden('handlebars')->registerHelper();
```

==== 

<a name="registerPartial"></a>

### registerPartial

Delays registering partials to the engine because there is no add partial method... 

#### Usage

```
eden('handlebars')->registerPartial(string $name, string $partial);
```

#### Parameters

 - `string $name` - the name of the helper
 - `string $partial` - the helper handler

Returns `Eden\Handlebrs\Index`

#### Example

```
eden('handlebars')->registerPartial();
```

==== 

<a name="setCachePath"></a>

### setCachePath

Enables the cache option

#### Usage

```
eden('handlebars')->setCachePath(string $cachePath);
```

#### Parameters

 - `string $cachePath` - The path to store cached copies of compiled templates

Returns `Eden\Handlebrs\Index`

#### Example

```
eden('handlebars')->setCachePath('/path/to/cache/folder');
```

====  

<a name="setCacheFilePrefix"></a>

### setCacheFilePrefix

Sets the cache file name prefix for caching

#### Usage

```
eden('handlebars')->setCacheFilePrefix(string $cacheFilePrefix);
```

#### Parameters

 - `string $cacheFilePrefix` - Custom prefix name

Returns `Eden\Handlebrs\Index`

#### Example

```
eden('handlebars')->setCacheFilePrefix('special-template-');
```

==== 

<a name="unregisterHelper"></a>

### unregisterHelper

The opposite of registerHelper 

#### Usage

```
eden('handlebars')->unregisterHelper(string $name);
```

#### Parameters

 - `string $name` - the helper name

Returns `Eden\Handlebars\Index`

#### Example

```
eden('handlebars')->unregisterHelper();
```

==== 

<a name="unregisterPartial"></a>

### unregisterPartial

The opposite of registerPartial 

#### Usage

```
eden('handlebars')->unregisterPartial(string $name);
```

#### Parameters

 - `string $name` - the partial name

Returns `Eden\Handlebars\Index`

#### Example

```
eden('handlebars')->unregisterPartial();
```

==== 

<a name="contributing"></a>
#Contributing to Eden

Contributions to *Eden* are following the Github work flow. Please read up before contributing.

##Setting up your machine with the Eden repository and your fork

1. Fork the repository
2. Fire up your local terminal create a new branch from the `v4` branch of your 
fork with a branch name describing what your changes are. 
 Possible branch name types:
    - bugfix
    - feature
    - improvement
3. Make your changes. Always make sure to sign-off (-s) on all commits made (git commit -s -m "Commit message")

##Making pull requests

1. Please ensure to run `phpunit` before making a pull request.
2. Push your code to your remote forked version.
3. Go back to your forked version on GitHub and submit a pull request.
4. An Eden developer will review your code and merge it in when it has been classified as suitable.
