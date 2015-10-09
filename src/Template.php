<?php
/*
 * This file is part of the System package of the Eden PHP Library.
 * (c) 2013-2014 Openovate Labs
 *
 * Copyright and license information can be found at LICENSE
 * distributed with this package.
 */

namespace Eden\Handlebars;

use Mustache_Context;

/**
 * Since the NoopCache now extends this, I can use my 
 * extended Context Class 
 *
 * @vendor Eden
 * @package Handlebars
 * @author Christian Blanquera cblanquera@openovate.com
 */
abstract class Template extends \Mustache_Template
{
    /**
     * Helper method to prepare the Context stack.
     *
     * Adds the Mustache HelperCollection to the stack's top context frame if helpers are present.
     *
     * @param mixed $context Optional first context frame (default: null)
     *
     * @return Mustache_Context
     */
    protected function prepareContextStack($context = null)
    {
		if(!($context instanceof Mustache_Context)) {
			$stack = new Context();
					
			if (!empty($context)) {
				$stack->push($context);
			}
		} else {
        	$stack = $context;
		}
		
        $helpers = $this->mustache->getHelpers();
		
		if (!$helpers->isEmpty()) {
            $stack->push($helpers);
        }

        return $stack;
    }
}
