<?php
/*
 * This file is part of the System package of the Eden PHP Library.
 * (c) 2013-2014 Openovate Labs
 *
 * Copyright and license information can be found at LICENSE
 * distributed with this package.
 */

namespace Eden\Handlebars;

/**
 * The HelperCollection has that nasty private $helpers 
 * property that can't be inherited. Mostly all is the 
 * same as Mustache_HelperCollection. I just needed to 
 * customize the get and has methods
 *
 * @vendor Eden
 * @package Handlebars
 * @author Christian Blanquera cblanquera@openovate.com
 */
class HelperCollection extends \Mustache_HelperCollection
{
    private $helpers = array();
	protected $handlebars = null;
	
    /**
     * Helper Collection constructor.
     *
     * Optionally accepts an array (or Traversable) of `$name => $helper` pairs.
     *
     * @throws Mustache_Exception_InvalidArgumentException if the $helpers argument isn't an array or Traversable
     *
     * @param array|Traversable $helpers (default: null)
     */
    public function __construct($helpers = null, $handlebars = null)
    {
		//CUSTOM
		$this->handlebars = $handlebars;
		//CUSTOM END
		
        parent::__construct($helpers);
    }
	
    /**
     * Add a helper to this collection.
     *
     * @param string $name
     * @param mixed  $helper
     */
    public function add($name, $helper)
    {
        $this->helpers[$name] = $helper;
    }

    /**
     * Get a helper by name.
     *
     * @throws Mustache_Exception_UnknownHelperException If helper does not exist.
     *
     * @param string $name
     *
     * @return mixed Helper
     */
    public function get($name)
    {
        if (!$this->has($name)) {
			throw new Exception($name);
        }
		
		//CUSTOM
		//if the name is a key
		if(isset($this->helpers[$name])) {
			//then return it
			return $this->helpers[$name];
		}
		
		//the same logic for has
		$start = strpos($name, ' ');
		
		//if there is no spaces
		//or the name is not a key
		if($start === false
			|| !isset($this->helpers[substr($name, 0, $start)])
		) {
			//uhh... something went wrong
			throw new Exception($name);
		}
	
        $helper = $this->helpers[substr($name, 0, $start)];
		$argString = substr($name, $start + 1);
		
		return function($source = null, $lambda = null) use ($helper, $argString) {
			return $helper($argString, $source, $lambda);
		};
		//END CUSTOM
    }

    /**
     * Check whether a given helper is present in the collection.
     *
     * @param string $name
     *
     * @return bool True if helper is present
     */
    public function has($name)
    {
		//CUSTOM
		$start = strpos($name, ' ');
		$first = $name;
		
		//if there is no spaces
		if($start !== false) {
			$first = substr($name, 0, $start);
		}
		
		if(!array_key_exists($first, $this->helpers) 
			&& $this->handlebars instanceof Index
		) {
			//some eden sugar
			$this->handlebars->trigger('handlebars-unknown', $first, substr($name, $start + 1));
		}
		//END CUSTOM
		
		return array_key_exists($first, $this->helpers);
    }

    /**
     * Check whether a given helper is present in the collection.
     *
     * @throws Mustache_Exception_UnknownHelperException if the requested helper is not present.
     *
     * @param string $name
     */
    public function remove($name)
    {
        if (!$this->has($name)) {
            throw new Exception($name);
        }

        unset($this->helpers[$name]);
    }

    /**
     * Clear the helper collection.
     *
     * Removes all helpers from this collection
     */
    public function clear()
    {
        $this->helpers = array();
    }

    /**
     * Check whether the helper collection is empty.
     *
     * @return bool True if the collection is empty
     */
    public function isEmpty()
    {
        return empty($this->helpers);
    }
}
