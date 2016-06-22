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
class Index extends \Eden\Core\Base
{
    const COMPILE_ERROR = "%s on line %s \n```\n%s\n```\n";

    const DEFAULT_CACHEFILE_PREFIX = '__HANDLEBARS__';

    /**
     * @var string
     */
    protected $cacheFilePrefix = self::DEFAULT_CACHEFILE_PREFIX;

    /**
     * The cache path location
     *
     * @var string|null
     */
    protected $cachePath = null;

    /**
     * A list of compiled template callbacks
     *
     * @var array
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
     * @param string $template The template string
     * @return function The template binding handler
     */
    public function compile(string $template)
    {
        $name = md5($template);

        if (isset(self::$callbacks[$name])) {
            return self::$callbacks[$name];
        }

        $callback = $this->loadCache($this->cacheFilePrefix . $name . '.php');
        if (!$callback) {
            $code = Compiler::i($this, $template)->compile();

            $this->saveCache($this->cacheFilePrefix . $name . '.php', $code);

            //called like: function($data) {};
            $callback = @eval('?>'.$code);
        }

        self::$callbacks[$name] = $callback;

        return $callback;
    }

    /**
     * @param string $filename
     * @return function|null
     */
    protected function loadCache(string $filename)
    {
        if ($this->cachePath === null) {
            return null;
        }
        if (is_dir($this->cachePath) === false) {
            return null;
        }
        $fullFilename = $this->cachePath . DIRECTORY_SEPARATOR . $filename;
        if (file_exists($fullFilename) === true) {
            return include($fullFilename);
        } else {
            return null;
        }
    }

    /**
     * @param string $filename
     * @param string $code
     */
    protected function saveCache(string $filename, string $code)
    {
        if ($this->cachePath === null) {
            return;
        }
        if (is_dir($this->cachePath) === false) {
            return;
        }
        $fullFilename = $this->cachePath . DIRECTORY_SEPARATOR . $filename;
        file_put_contents($filename, $code);
    }

    /**
     * Returns the cache location
     *
     * @return string|null
     */
    public function getCachePath()
    {
        return $this->cachePath;
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
     * Sets a path to store cached copies of compiled templates.
     * Setting this enables caching.
     *
     * @param string $cachePath The cache path
     * @return Index
     */
    public function setCachePath(string $cachePath) : Index
    {
        $this->cachePath = $cachePath;
        return $this;
    }

    /**
     * Sets the cache file name prefix
     *
     * @param string $cacheFilePrefix
     * @return Index
     */
    public function setCacheFilePrefix(string $cacheFilePrefix) : Index
    {
        $this->cacheFilePrefix = $cacheFilePrefix;
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
}
