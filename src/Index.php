<?php //-->
declare(strict_types=1);
/**
 * This file is part of the Eden PHP Library.
 * (c) 2014-2016 Openovate Labs
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

namespace Eden\Handlebars;

/**
 * Welcome to Eden\Handlebars!
 *
 * This definition wraps the Engine to match
 * the handlebars API as close as possible
 *
 * @vendor   Eden
 * @package  handlebars
 * @author   Christian Blanquera <cblanquera@openovate.com>
 * @standard PSR-2
 */
class Index extends Base
{
    /**
     * @const string FILE_PREFIX
     */
    const COMPILE_ERROR = "%s on line %s \n```\n%s\n```\n";

    /**
     * @const string FILE_PREFIX
     */
    const FILE_PREFIX = '__HANDLEBARS__';

    /**
     * @var string $prefix You can change the file prefix with setPrefix()
     */
    protected $prefix = self::FILE_PREFIX;

    /**
     * @var string|null $cache The cache path location
     */
    protected $cache = null;

    /**
     * @var array $callbacks A list of compiled template callbacks
     */
    protected static $callbacks = array();
    
    /**
     * Just load the default helpers
     */
    public function __construct()
    {
        $helpers = require(__DIR__ . '/helpers.php');
        
        foreach ($helpers as $name => $helper) {
            $this->registerHelper($name, $helper);
        }
    }

    /**
     * Returns a callback that binds the data with the template
     *
     * @param string $template the template string
     * @return function The template binding handler
     */
    public function compile(string $template)
    {
        $name = md5($template);
        
        if (isset(self::$callbacks[$name])) {
            return self::$callbacks[$name];
        }
        
        $file = $this->cache . '/' . $this->prefix . $name . '.php';

        if (is_dir($this->cache) && file_exists($file)) {
            $callback = include($file);
        } else {
            $code = Compiler::i($this, $template)->compile();
            
            if (is_dir($this->cache)) {
                file_put_contents($file, $code);
            }
            
            //called like: function($data) {};
            $callback = @eval('?>'.$code);
            //$this->checkEval($code);
        }
        
        self::$callbacks[$name] = $callback;
        
        return $callback;
    }

    /**
     * Returns the cache location
     *
     * @return string|null
     */
    public function getCache()
    {
        return $this->cache;
    }

    /**
     * Returns a specific helper
     *
     * @param string $name The name of the helper
     * @return function|null
     */
    public function getHelper(string $name)
    {
        return Runtime::getHelper($name);
    }

    /**
     * Returns all the registered helpers
     *
     * @return array
     */
    public function getHelpers() : array
    {
        return Runtime::getHelpers();
    }

    /**
     * Returns a specific partial
     *
     * @param string $name The name of the helper
     * @return string|null
     */
    public function getPartial(string $name)
    {
        return Runtime::getPartial($name);
    }

    /**
     * Returns all the registered partials
     *
     * @return array
     */
    public function getPartials() : array
    {
        return Runtime::getPartials();
    }

    /**
     * The famous register helper matching the Handlebars API
     *
     * @param string $name   The name of the helper
     * @param function $helper The helper handler
     * @return Index
     */
    public function registerHelper(string $name, $helper) : Index
    {
        Runtime::registerHelper($name, $helper);
        return $this;
    }

    /**
     * Delays registering partials to the engine
     * because there is no add partial method...
     *
     * @param string $name The name of the helper
     * @param string $partial The helper handler
     * @return Index
     */
    public function registerPartial(string $name, string $partial) : Index
    {
        Runtime::registerPartial($name, $partial);
        return $this;
    }

    /**
     * Resets the helpers and partials
     *
     * @return Index
     */
    public function reset() : Index
    {
         Runtime::flush();
         $this->__construct();
         return $this;
    }

    /**
     * Enables the cache option
     *
     * @param string The cache path
     * @return Index
     */
    public function setCache(string $path) : Index
    {
        $this->cache = $path;
        return $this;
    }

    /**
     * Sets the file name prefix
     *
     * @param string $prefix
     * @return Index
     */
    public function setPrefix(string $prefix) : Index
    {
        $this->prefix = $prefix;
        return $this;
    }

    /**
     * The opposite of registerHelper
     *
     * @param string $name the helper name
     * @return Index
     */
    public function unregisterHelper(string $name) : Index
    {
        Runtime::unregisterHelper($name);
        return $this;
    }

    /**
     * The opposite of registerPartial
     *
     * @param string $name the partial name
     * @return Index
     */
    public function unregisterPartial(string $name) : Index
    {
        Runtime::unregisterPartial($name);
        return $this;
    }

    /**
     * Returns a very nice error message
     *
     * @param string $code
     * @return Index
     */
    protected function checkEval(string $code) : Index
    {
        $error = error_get_last();

        if (isset($error['message']) 
			&& isset($error['line'])
			&& $error['message'] === 'parse error'
		) {
            $code = explode("\n", $code);
            $start = $error['line'] - 25;
            if ($start < 0) {
                $start = 0;
            }

            $code = array_splice($code, $start, 50);

            foreach ($code as $i => $line) {
                $code[$i] = (++$start) . ': ' . $line;
            }

            Exception::i(self::COMPILE_ERROR)
                ->setType('COMPILE')
                ->addVariable($error['message'])
                ->addVariable($error['line'])
                ->addVariable(implode("\n", $code))
                ->trigger();
        }

        return $this;
    }
}
