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
    - [getContext](#getContext)
    - [getEngine](#getEngine)
    - [getHelpers](#getHelpers)
    - [getLambdaHelper](#getLambdaHelper)
    - [getOptions](#getOptions)
    - [getPartials](#getPartials)
    - [getTemplate](#getTemplate)
    - [parseArguments](#parseArguments)
    - [registerHelper](#registerHelper)
    - [registerPartial](#registerPartial)
    - [setContext](#setContext)
    - [setEngine](#setEngine)
    - [setTemplate](#setTemplate)
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

We formerly used Xamin/Handlebars, however after a few projects, attempting to contribute some fixes 
and the greater need for handlebars.js intermediate features to work, we decided to completely reinvent 
Handlebars for PHP. Upon looking at other possible libraries and their respective implementation, 
we did not agree completely with the underlying design. Most try to copy the files Mustache.php and 
use that as a base or trying to build handlebars completely from scratch. Even Mustache.php is not 
without its design flaws. Our approach is to extend the existing library of Mustache.php adding in 
only the things we need to turn mustache into handlebars matching the exact API of both handlebars.js
and mustache.js alike. The result is less files to maintain as our own code is just for handlebars.

Because of the mentioned design flaws of Mustache.php there were a few classes that needed to be 
copied to our library and slightly customized in which they have the changes documented appropriately.

 - Mustache_Context to Context.php
     - findVariableInStack() - add in cases for helpers and arguments
	 - findAnchoredDot() - add in cases for helper arguments
	 - findDot() - add in cases for literal values (vs helpers), parent variables and helper arguments
 - Mustache_Engine to Engine.php
     - getHelpers() - needed to use our HelperCollection instead
	 - getLambdaCache() - needed to use our NoopCache
	 - loadPartial() - needed a way for partials to pass arguments and partials to be callable
 - Mustache_HelperCollection to HelperCollection.php
     - get() - pass helpers with arguments
	 - has() - pass helpers with arguments
 - Mustache_Cache_NoopCache to NoopCache.php
     - cache() - needed to extend Eden\Handlebars\Template instead of Mustache_Template
 - Mustache_Parser to Parser.php 
     - buildTree() - just needed to allow helpers with arguments
 - Mustache_Template to Template.php
     - prepareContextStack() - needed a way to accept Eden\Handlebars\Context instead of just a raw array

On top of this list each file has been marked with what exactly we changed within each class to make it 
easier to compare against the original Mustache.php code and ours.

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
	 - Partials accepts arguments
 - Default Helpers matching handlebars.js
     - each - and `{{#each foo as |value, key|}}`
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
 - Enable cache by using `eden('handlebars')->setCache(__DIR__.'/your/cache/folder/location');`
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

<a name="getContext"></a>

### getContext

Returns the current context 

#### Usage

```
eden('handlebars')->getContext();
```

#### Parameters

Returns `Eden\Handlebars\Context`

==== 

<a name="getEngine"></a>

### getEngine

Returns the current Mustache/Handlebars Engine 

#### Usage

```
eden('handlebars')->getEngine();
```

#### Parameters

Returns `Eden\Handlebars\Engine`

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

<a name="getLambdaHelper"></a>

### getLambdaHelper

Factory for Mustache_LambdaHelper 

#### Usage

```
eden('handlebars')->getLambdaHelper();
```

#### Parameters

Returns `array`

==== 

<a name="getOptions"></a>

### getOptions

Generates options used for helpers and partials 

#### Usage

```
eden('handlebars')->getOptions(string $source, function|null $helper, Mustache_LambdaHelper|null $lambda, string $argString, array $hash);
```

#### Parameters

 - `string $source` - The template block
 - `function|null $helper` - The raw helper handler
 - `Mustache_LambdaHelper|null $lambda` - The lambda helper renderer
 - `string $argString` - The raw argument string
 - `array $hash` - Any key/value to pass along

Returns `array`

#### Example

```
eden('handlebars')->getOptions();
```

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

<a name="getTemplate"></a>

### getTemplate

Returns the initial string template 

#### Usage

```
eden('handlebars')->getTemplate();
```

#### Parameters

Returns `string`

==== 

<a name="parseArguments"></a>

### parseArguments

Mustache will give arguments in a string This will transform them into a legit argument array 

#### Usage

```
eden('handlebars')->parseArguments();
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

<a name="setContext"></a>

### setContext

You may set the initial context if you wish 

#### Usage

```
eden('handlebars')->setContext(array|Eden\Handlebars\Context $context);
```

#### Parameters

 - `array|Eden\Handlebars\Context $context` - The prescribed context

Returns `Eden\Handlebrs\Index`

#### Example

```
eden('handlebars')->setContext();
```

==== 

<a name="setEngine"></a>

### setEngine

You may set the entire engine if you wish 

#### Usage

```
eden('handlebars')->setEngine(Eden\Handlebars\Engine $engine);
```

#### Parameters

 - `Eden\Handlebars\Engine $engine` - The prescribed engine

Returns `Eden\Handlebrs\Index`

#### Example

```
eden('handlebars')->setEngine();
```

==== 

<a name="setTemplate"></a>

### setTemplate

You may set the initial template if you wish 

#### Usage

```
eden('handlebars')->setTemplate(string $template);
```

#### Parameters

 - `string $template` - The prescribed tempalte

Returns `Eden\Handlebrs\Index`

#### Example

```
eden('handlebars')->setTemplate();
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