<?php
declare(strict_types=1);
/**
 * This file was formerly part of the Eden PHP Library.
 * (c) 2014-2016 Openovate Labs
 * (c) 2016 Matthew Gamble
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

namespace MattyG\Handlebars;

class Handlebars
{
    const VERSION = "6.0.0-dev1";

    const COMPILE_ERROR = "%s on line %s \n```\n%s\n```\n";

    const DEFAULT_NAME_PREFIX = '__HANDLEBARS__';

    /**
     * @var Runtime
     */
    protected $runtime;

    /**
     * @var Compiler
     */
    protected $compiler;

    /**
     * @param DataFactory
     */
    protected $dataFactory;

    /**
     * The cache path location
     *
     * @var string|null
     */
    protected $cachePath = null;

    /**
     * @var string
     */
    protected $namePrefix = self::DEFAULT_NAME_PREFIX;

    /**
     * List of compiled templates
     *
     * @var array
     */
    protected $compiledTemplates = array();

    /**
     * @var string
     */
    protected static $layout = null;

    /**
     * @param Runtime $runtime
     * @param Compiler $compiler
     */
    public function __construct(Runtime $runtime, Compiler $compiler, DataFactory $dataFactory)
    {
        $this->runtime = $runtime;
        $this->compiler = $compiler;
        $this->dataFactory = $dataFactory;

        if (is_null(self::$layout)) {
            self::$layout = file_get_contents(__DIR__ . '/layout.template');
        }
    }

    /**
     * Returns a callback that binds the data with the template
     * TODO: Update return type
     *
     * @param string $source The template string
     * @return callable The template binding handler
     */
    public function compile(string $source)
    {
        $name = $this->namePrefix . md5(self::VERSION . $source);

        if (isset($this->compiledTemplates[$name])) {
            goto returner;
        } elseif (class_exists($name) === true) {
            goto instantiator;
        }

        if ($this->loadCache($name . '.php') === false) {
            $code = $this->compiler->compile($source);
            $formattedCode = sprintf(self::$layout, $name, $code);
            $this->saveCache($name . '.php', $formattedCode);
            eval('?>' . $formattedCode);
        }

instantiator:
        $this->compiledTemplates[$name] = new $name($this->runtime, $this->dataFactory);
returner:
        return $this->compiledTemplates[$name];
    }

    /**
     * @param string $filename
     * @return bool
     */
    protected function loadCache(string $filename) : bool
    {
        if ($this->cachePath === null) {
            return false;
        }
        if (is_dir($this->cachePath) === false) {
            return false;
        }
        $fullFilename = $this->cachePath . DIRECTORY_SEPARATOR . $filename;
        if (file_exists($fullFilename) === true) {
            require($fullFilename);
            return true;
        } else {
            return false;
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
     * The famous register helper matching the Handlebars API
     *
     * @param string $name The name of the helper
     * @param callable $helper The helper handler
     * @return Handlebars
     */
    public function registerHelper(string $name, $helper) : Handlebars
    {
        $this->runtime->addHelper($name, $helper);
        return $this;
    }

    /**
     * Returns a specific helper
     * TODO: Remove this
     *
     * @param string $name The name of the helper
     * @return callable|null
     */
    public function getHelper(string $name)
    {
        return $this->runtime->getHelper($name);
    }

    /**
     * Delays registering partials to the engine
     * because there is no add partial method...
     *
     * @param string $name The name of the partial
     * @param string $partial
     * @return Handlebars
     */
    public function registerPartial(string $name, string $partial) : Handlebars
    {
        $this->runtime->addPartial($name, $partial);
        return $this;
    }

    /**
     * Returns a specific partial
     * TODO: Remove this
     *
     * @param string $name The name of the helper
     * @return string|null
     */
    public function getPartial(string $name)
    {
        return $this->runtime->getPartial($name);
    }

    /**
     * Sets a path to store cached copies of compiled templates.
     * Setting this enables caching.
     *
     * @param string $cachePath The cache path
     * @return Handlebars
     */
    public function setCachePath(string $cachePath) : Handlebars
    {
        $this->cachePath = $cachePath;
        return $this;
    }

    /**
     * Sets the name prefix
     *
     * @param string $namePrefix
     * @return Handlebars
     */
    public function setNamePrefix(string $namePrefix) : Handlebars
    {
        $this->namePrefix = $namePrefix;
        return $this;
    }
}
