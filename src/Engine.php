<?php
/**
 * This file is part of the Eden package.
 * (c) 2014-2016 Openovate Labs
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

namespace Eden\Handlebars;

/**
 * Again, The engine is riddled with private methods
 * and properties that can't be inherited. Not that bad here
 * however. I just needed to mod the helpers to use my extended
 * helper collection class and cache to replace the compiled
 * code to extend my extended abstract template
 *
 * @vendor   Eden
 * @package  Handlebars
 * @author   Christian Blanquera <cblanquera@openovate.com>
 * @standard PSR-2
 */
class Engine extends \Mustache_Engine
{
    protected $handlebars;
    
    private $helpers;
    private $cache;
    private $lambdaCache;
    
    /**
     * Mustache class constructor.
     *
     * Passing an $options array allows overriding certain Mustache options during instantiation:
     *
     *     $options = array(
     *         // The class prefix for compiled templates. Defaults to '__Mustache_'.
     *         'template_class_prefix' => '__MyTemplates_',
     *
     *         // A Mustache cache instance or a cache directory string for compiled templates.
     *         // Mustache will not cache templates unless this is set.
     *         'cache' => dirname(__FILE__).'/tmp/cache/mustache',
     *
     *         // Override default permissions for cache files. Defaults to using the system-defined umask. It is
     *         // *strongly* recommended that you configure your umask properly rather than overriding permissions here.
     *         'cache_file_mode' => 0666,
     *
     *         // Optionally, enable caching for lambda section templates. This is generally not recommended, as lambda
     *         // sections are often too dynamic to benefit from caching.
     *         'cache_lambda_templates' => true,
     *
     *         // A Mustache template loader instance. Uses a StringLoader if not specified.
     *         'loader' => new Mustache_Loader_FilesystemLoader(dirname(__FILE__).'/views'),
     *
     *         // A Mustache loader instance for partials.
     *         'partials_loader' => new Mustache_Loader_FilesystemLoader(dirname(__FILE__).'/views/partials'),
     *
     *         // An array of Mustache partials. Useful for quick-and-dirty string template loading, but not as
     *         // efficient or lazy as a Filesystem (or database) loader.
     *         'partials' => array('foo' => file_get_contents(dirname(__FILE__).'/views/partials/foo.mustache')),
     *
     *         // An array of 'helpers'. Helpers can be global variables or objects, closures (e.g. for higher order
     *         // sections), or any other valid Mustache context value. They will be prepended to the context stack,
     *         // so they will be available in any template loaded by this Mustache instance.
     *         'helpers' => array('i18n' => function ($text) {
     *             // do something translatey here...
     *         }),
     *
     *         // An 'escape' callback, responsible for escaping double-mustache variables.
     *         'escape' => function ($value) {
     *             return htmlspecialchars($buffer, ENT_COMPAT, 'UTF-8');
     *         },
     *
     *         // Type argument for `htmlspecialchars`.  Defaults to ENT_COMPAT.  You may prefer ENT_QUOTES.
     *         'entity_flags' => ENT_QUOTES,
     *
     *         // Character set for `htmlspecialchars`. Defaults to 'UTF-8'. Use 'UTF-8'.
     *         'charset' => 'ISO-8859-1',
     *
     *         // A Mustache Logger instance. No logging will occur unless this is set. Using a PSR-3 compatible
     *         // logging library -- such as Monolog -- is highly recommended. A simple stream logger implementation is
     *         // available as well:
     *         'logger' => new Mustache_Logger_StreamLogger('php://stderr'),
     *
     *         // Only treat Closure instances and invokable classes as callable. If true, values like
     *         // `array('ClassName', 'methodName')` and `array($classInstance, 'methodName')`, which are traditionally
     *         // "callable" in PHP, are not called to resolve variables for interpolation or section contexts. This
     *         // helps protect against arbitrary code execution when user input is passed directly into the template.
     *         // This currently defaults to false, but will default to true in v3.0.
     *         'strict_callables' => true,
     *
     *         // Enable pragmas across all templates, regardless of the presence of pragma tags in the individual
     *         // templates.
     *         'pragmas' => [Mustache_Engine::PRAGMA_FILTERS],
     *     );
     *
     * @throws Mustache_Exception_InvalidArgumentException If `escape` option is not callable.
     *
     * @param array $options (default: array())
     */
    public function __construct(array $options = array(), $handlebars = null)
    {
        $this->handlebars = $handlebars;
        parent::__construct($options);
    }
    
    /**
     * Set an array of Mustache helpers.
     *
     * An array of 'helpers'. Helpers can be global variables or objects, closures (e.g. for higher order sections), or
     * any other valid Mustache context value. They will be prepended to the context stack, so they will be available in
     * any template loaded by this Mustache instance.
     *
     * @throws Mustache_Exception_InvalidArgumentException if $helpers is not an array or Traversable
     *
     * @param array|Traversable $helpers
     */
    public function setHelpers($helpers)
    {
        if ($helpers instanceof \Mustache_HelperCollection) {
            $this->helpers = $helpers;
            return $helpers;
        } else if (!is_array($helpers) && !$helpers instanceof Traversable) {
            throw new Exception('setHelpers expects an array of helpers');
        }

        $this->getHelpers()->clear();

        foreach ($helpers as $name => $helper) {
            $this->addHelper($name, $helper);
        }
        
        return $helpers;
    }
    
    /**
     * Get the current set of Mustache helpers.
     *
     * @see Mustache_Engine::setHelpers
     *
     * @return Mustache_HelperCollection
     */
    public function getHelpers()
    {
        if (!isset($this->helpers)) {
            $this->helpers = new HelperCollection(null, $this->handlebars);
        }

        return $this->helpers;
    }

    /**
     * Add a new Mustache helper.
     *
     * @see Mustache_Engine::setHelpers
     *
     * @param string $name
     * @param mixed  $helper
     */
    public function addHelper($name, $helper)
    {
        $this->getHelpers()->add($name, $helper);
    }

    /**
     * Get the current Mustache Cache instance.
     *
     * If no Cache instance has been explicitly specified, this method will instantiate and return a new one.
     *
     * @return Mustache_Cache
     */
    public function getCache()
    {
        if (!isset($this->cache)) {
            $this->cache = new NoopCache();
            $this->setCache($this->cache);
        }

        return $this->cache;
    }
    
    /**
     * Get the current Lambda Cache instance.
     *
     * If 'cache_lambda_templates' is enabled, this is the default cache instance. Otherwise, it is a NoopCache.
     *
     * @see Mustache_Engine::getCache
     *
     * @return Mustache_Cache
     */
    protected function getLambdaCache()
    {
        if (!isset($this->lambdaCache)) {
            $this->lambdaCache = new NoopCache();
        }

        return $this->lambdaCache;
    }
    
    

    /**
     * Load a Mustache partial Template by name.
     *
     * This is a helper method used internally by Template instances for loading partial templates. You can most likely
     * ignore it completely.
     *
     * @param string $name
     *
     * @return Mustache_Template
     */
    public function loadPartial($name)
    {
        if (!$this->handlebars) {
            return parent::loadPartial($name);
        }
        
        //the partial list from handlebars
        $partials = $this->handlebars->getPartials();
        
        $partial = $name;
        //if there's a space, it means they are trying to
        //pass a scope into the partial. We need a different
        //way to do this
        if (strpos($name, ' ') !== false) {
            list($partial, $scope) = explode(' ', $name, 2);
            
            //get the scope by evaluating
            list($args, $hash) = $this->handlebars->parseArguments($scope);
            
            //if there are no arguments
            if (empty($args)) {
                //args 0 will be the scope
                $args[] = array();
            }
            
            //merge the hash with the scope
            $args[0] = array_merge($args[0], $hash);
        //if there are no arguments, but the partial is
        //a callback, we still need another way to do this
        } else if (isset($partials[$name])
            && is_callable($partials[$name])
        ) {
            $args = array();
        }
        
        //if there are arguments, it must have
        //come from the above if statements
        if (isset($partials[$partial]) && isset($args)) {
            //assume that the partial is callable
            $source = '';
            $helper = $partials[$partial];
            
            //but if the partial is a string
            if (is_string($partials[$partial])) {
                //the source is the partial
                $source = $partials[$partial];
                //create the helper
                $helper = function ($scope, $options) use ($source) {
                    $args = func_get_args();
                    $options = array_pop($args);
                    
                    return $options['fn']($scope);
                };
            }
            
            //use a helper to resolve the case
            
            //add in the options
            $args[] = $this->handlebars->getOptions($source, $helper);
            
            //bind the context
            return new Partial($this, $helper, $args);
        }
        
        return parent::loadPartial($partial);
    }
    
    /**
     * Set the Mustache Cache instance.
     *
     * @param Mustache_Cache $cache
     */
    public function setCache(\Mustache_Cache $cache)
    {
        $this->cache = $cache;
        
        return $this;
    }
}
