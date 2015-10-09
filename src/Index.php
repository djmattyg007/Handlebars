<?php //-->
/*
 * This file is part of the Template package of the Eden PHP Library.
 * (c) 2013-2014 Openovate Labs
 *
 * Copyright and license information can be found at LICENSE
 * distributed with this package.
 */

namespace Eden\Handlebars;

/**
 * Welcome to Eden\Handlebars!
 * 
 * This definition wraps the Engine to match
 * the handlebars API as close as possible
 *
 * @vendor Eden
 * @package handlebars
 * @author Christian Blanquera cblanquera@openovate.com
 */
class Index extends Base
{
	protected $engine = null;
	protected $context = null;
	protected $template = null;
	protected $partials = array();
	
	/**
	 * Returns a callback that binds the data with the template
	 *
	 * @param string $string the template string
	 * 
	 * @return function the template binding handler
	 */
	public function compile($string)
	{
		//get handlebars
		$handlebars = $this;
		
		//trim the template
		$string = $this->trimBars($string);
		
		//set template
		$this->setTemplate($string);
		
		//set parser
		$handlebars->getEngine()->setParser(new Parser());
		
		//set partials
		$handlebars->getEngine()->setPartials($this->partials);
		
		//set default helpers
		$helpers = include 'helpers.php';
		
		foreach($helpers as $name => $helper) {
			$handlebars->registerHelper($name, $helper);
		}
		
		//return the template callback like handlebars.js
		return function($data = array()) use ($handlebars) {
			//save and get the context
			$context = $handlebars->setContext($data)->getContext();
			$template = $handlebars->getTemplate();
			//and render
			return $handlebars->getEngine()->render($template, $context);	
		};
	}
	
	/**
	 * Returns the current context
	 *
	 * @return Eden\Handlebars\Context
	 */
	public function getContext()
	{
		//if no context
		if(!$this->context) {
			//just make an empty context
			$this->setContext();
		}
		
		return $this->context;
	}
	
	/**
	 * Returns the current Mustache/Handlebars Engine
	 *
	 * @return Eden\Handlebars\Engine
	 */
	public function getEngine()
	{
		if(!$this->engine) {
			$this->setEngine(new Engine(array(), $this));
		}
		
		return $this->engine;
	}
	
	/**
	 * Generates options used for helpers and partials
	 *
	 * @param string $source the template block
	 * @param function|null $helper the raw helper handler
	 * @param Mustache_LambdaHelper|null $lambda the lambda helper renderer
	 * @param string $argString the raw argument string
	 * @param array $hash any key/value to pass along
	 *
	 * @return array
	 */
	public function getOptions(
		$source = '', 
		$helper = null,
		$lambda = null,
		$argString = '',
		array $hash = array()
	) {
		$sourceSuccess = $source;
		$sourceFail = '';
		if(strpos($source, '{{else}}') !== false) {
			list($sourceSuccess, $sourceFail) = explode('{{else}}', $source, 2);
		}
		
		if(!$helper) {
			$helper = function() {};
		}
		
		$engine = $this->getEngine();
		$context = $this->getContext();
		
		if(!$lambda) {
			$lambda = new \Mustache_LambdaHelper($engine, $context);
		}
		
		return array(
			//some very useful options
			//for people that are like me :)
			'handlebars' => $this,
			'success' => $sourceSuccess,
			'source' => $source,
			'helper' => $helper,
			'lambda' => $lambda,
			'args' => $argString,
			'fail' => $sourceFail,
			'hash' => $hash,
			
			'fn' => function($data = null) use (
				$sourceSuccess,
				$lambda, 
				$context
			) {
				//if what they gave us was the context back
				if($data === $context || is_null($data)) {
					//set it to the last in the stack
					$data = $context->last();
				}
				
				$context->push($data);
				
				//if no source or lambda
				if(!$sourceSuccess || !$lambda) {
					return '';
				}
				
				$results = $lambda->render($sourceSuccess);
				
				$context->pop();
				
				return $results;
			},
			'inverse' => function($data = null) use (
				$sourceFail,
				$lambda, 
				$context
			) {
				//if what they gave us was the context back
				if($data === $context || is_null($data)) {
					//set it to the last in the stack
					$data = $context->last();
				}
				
				$context->push($data);
				
				//if no source or lambda
				if(!$sourceFail || !$lambda) {
					return '';
				}
				
				$results = $lambda->render($sourceFail);
				
				$context->pop();
				
				return $results;
			}
		);
	}
	
	/**
	 * Returns all the registered partials
	 *
	 * @return array
	 */
	public function getPartials()
	{
		return $this->partials;
	}
	
	/**
	 * Returns the initial string template
	 *
	 * @return string
	 */
	public function getTemplate()
	{
		return $this->template;
	}
	
	/**
	 * Mustache will give arguments in a string
	 * This will transform them into a legit argument
	 * array
	 *
	 * @return array
	 */
	public function parseArguments($string)
	{
		$args = array();
		$hash = array();
		
		$stringArgs = $this->getArgumentsFromString($string);
		
		$hashRegex = array(
			'([a-zA-Z0-9]+\="[^"]*")', 		// cat="meow"
			'([a-zA-Z0-9]+\=\'[^\']*\')', 	// mouse='squeak squeak'
			'([a-zA-Z0-9]+\=[a-zA-Z0-9]+)', // dog=false
		);
		
		foreach($stringArgs as $arg) {
			$value = $this->getLiteralValue($arg);
			
			//if the value changed
			if($value !== $arg) {
				$args[] = $value;
				continue;
			}
			
			//if it's an attribute
			if(preg_match('#'.implode('|', $hashRegex).'#is', $arg)) {
				list($hashKey, $hashValue) = explode('=', $arg, 2);
				
				$value = $this->getLiteralValue($hashValue);
				
				if($value === $hashValue) {
					$value = $this->getContext()->find($value);
				}
				
				$hash[$hashKey] = $value;
				continue;
			}		
			
			//it's a variable name
			//lets find it in the context
			$args[] = $this->getContext()->find($arg);
		}
		
		return array($args, $hash);
	}
	
	/**
	 * The famous register helper matching the Handlebars API
	 *
	 * @param string   $name   the name of the helper
	 * @param function $helper the helper handler
	 *
	 * @return Eden\Handlebrs\Index
	 */
	public function registerHelper($name, $helper)
	{
		$handlebars = $this;
		//make a wrapper for this helper to match what
		//the original handlebars.js API expects
		$callback = function($argString, $source, $lambda) use ($handlebars, $helper) {
			//remember the context
			$context = $handlebars->getContext();
			
			//now we need a way to parse the arg string
			//we can't just split by space ...
			list($args, $hash) = $handlebars->parseArguments($argString);
			
			//options
			$args[] = $this->getOptions(
				$source, 
				$helper,
				$lambda,
				$argString,
				$hash);
			
			//bind the context
			$bound = $helper->bindTo($context, get_class($context));
			return call_user_func_array($bound, $args);
		};
		
		//get engine add helper wrapper
		$this->getEngine()->addHelper($name, $callback);
		
		return $this;
	}
	
	/**
	 * Delays registering partials to the engine
	 * because there is no add partial method...
	 *
	 * @param string $name    the name of the helper
	 * @param string $partial the helper handler
	 *
	 * @return Eden\Handlebrs\Index
	 */
	public function registerPartial($name, $partial)
	{
		$this->partials[$name] = $partial;
		return $this;
	}
	
	/**
	 * You may set the initial context if you wish
	 *
	 * @param array|Eden\Handlebars\Context $context The prescribed context
	 *
	 * @return Eden\Handlebrs\Index
	 */
	public function setContext($context = null)
	{
		//if context is not a Context
		if(!($context instanceof \Mustache_Context)) {
			//make context a context
			$context = new Context($context, $this);
		}
		
		$this->context = $context;
		
		return $this;
	}
	
	/**
	 * You may set the entire engine if you wish
	 *
	 * @param Eden\Handlebars\Engine $context The prescribed engine
	 *
	 * @return Eden\Handlebrs\Index
	 */
	public function setEngine($engine)
	{
		$this->engine = $engine;
		
		return $this;
	}
	
	/**
	 * You may set the initial template if you wish
	 *
	 * @param string $template The prescribed tempalte
	 *
	 * @return Eden\Handlebrs\Index
	 */
	public function setTemplate($template)
	{
		$this->template = $template;
		
		return $this;
	}
	
	/**
	 * If there's a quote, null, bool, 
	 * int, float... it's the literal value
	 *
	 * @param string $value One string argument value
	 *
	 * @return mixed
	 */
	protected function getLiteralValue($value) 
	{
		//if it's a literal string value
		if(strpos($value, '"') === 0
			|| strpos($value, "'") === 0
		) {
			return substr($value, 1, -1);
		}
		
		//if it's null
		if(strtolower($value) === 'null') {
			return null;
		}
		
		//if it's a bool
		if(strtolower($value) === 'true') {
			return true;
		}
		
		if(strtolower($value) === 'false') {
			return false;
		}
		
		//if it's a number
		if(is_numeric($value)) {
			return (float) $value;
		}	
		
		return $value;
	}
	
	/**
	 * Sparses out the inital argument string
	 *
	 * @param string $string the argument string
	 *
	 * @return array
	 */
	protected function getArgumentsFromString($string)
	{
		$regex = array(
			'([a-zA-Z0-9]+\="[^"]*")', 		// cat="meow"
			'([a-zA-Z0-9]+\=\'[^\']*\')', 	// mouse='squeak squeak'
			'([a-zA-Z0-9]+\=[a-zA-Z0-9]+)', // dog=false
			'("[^"]*")',					// "some\'thi ' ng"
			'(\'[^\']*\')',					// 'some"thi " ng'
			'([^\s]+)'						// <any group with no spaces>
		);
		
		preg_match_all('#'.implode('|', $regex).'#is', $string, $matches);
		
		return $matches[0];
	}
	
	/**
	 * Replaces ~ with trims
	 *
	 * @param string $string the template string
	 *
	 * @return string
	 */
	protected function trimBars($string)
	{
		$string = preg_replace('#\s*\{\{\~\s*#is', '{{', $string);
		$string = preg_replace('#\s*\~\}\}\s*#is', '}}', $string);
		return $string;
	}
	
	/**
	 * The opposite of registerHelper
	 *
	 * @param string $name the helper name
	 *
	 * @return Eden\Handlebars\Index
	 */
	protected function unregisterHelper($name)
	{
		$this->getEngine()->removeHelper($name);
		
		return $this;
	}
	
	/**
	 * The opposite of registerPartial
	 *
	 * @param string $name the partial name
	 *
	 * @return Eden\Handlebars\Index
	 */
	protected function unregisterPartial($name)
	{
		if(isset($this->partials[$name])) {
			unset($this->partials[$name]);
		}
	}
}