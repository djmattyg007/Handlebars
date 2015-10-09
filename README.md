![logo](http://eden.openovate.com/assets/images/cloud-social.png) Eden Handlebars
====

- [Install](#install)
- [Introduction](#intro)
- [Usage](#usage)
- [Features](#features)
- [De-Features](#defeatures)
- [Contributing](#contributing)

====

<a name="install"></a>
## Install

`composer install eden/handlebars`

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