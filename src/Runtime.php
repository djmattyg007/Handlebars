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

class Runtime
{
    /**
     * A raw list of helpers
     *
     * @var callable[]
     */
    protected $helpers = array();

    /**
     * A raw list of partials
     *
     * @var string[]
     */
    protected $partials = array();

    public function __construct($addDefaultHelpers = true)
    {
        if ($addDefaultHelpers === true) {
            $helpers = require(__DIR__ . '/helpers.php');
            foreach ($helpers as $name => $helper) {
                $this->addHelper($name, $helper);
            }
        }
    }

    /**
     * @param string $name The name of the helper
     * @param callable $helper The helper
     */
    public function addHelper(string $name, $helper)
    {
        if (is_callable($helper) === false) {
            throw new Exception("All Handlebars helpers must be callable");
        }
        $this->helpers[$name] = $helper;
    }

    /**
     * Returns a specific helper
     *
     * @param string $name The name of the helper
     * @return callable|null
     */
    public function getHelper(string $name)
    {
        if (isset($this->helpers[$name])) {
            return $this->helpers[$name];
        }

        return null;
    }

    /**
     * Delays registering partials to the engine
     * because there is no add partial method...
     *
     * @param string $name The name of the helper
     * @param string $partial The helper handler
     */
    public function addPartial(string $name, string $partial)
    {
        $this->partials[$name] = $partial;
    }

    /**
     * Returns a specific partial
     *
     * @param string $name The name of the helper
     * @return string|null
     */
    public function getPartial(string $name)
    {
        if (isset($this->partials[$name])) {
            return $this->partials[$name];
        }

        return null;
    }
}
