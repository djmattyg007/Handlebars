<?php
/**
 * This file is part of the Eden PHP Library.
 * (c) 2014-2016 Openovate Labs
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

namespace Eden\Handlebars;

/**
 * The same as Mustache_Context except for findVariableInStack
 * Since every method in this class had private methods and
 * properties I couldn't remove anything. So much for inheritence.
 * I made sure that every copied method was needed here.
 *
 * @vendor   Eden
 * @package  Handlebars
 * @author   Christian Blanquera <cblanquera@openovate.com>
 * @standard PSR-2
 */
class Context extends \Mustache_Context
{
    private $stack      = array();
    private $blockStack = array();

    /**
     * Mustache rendering Context constructor.
     *
     * @param mixed $context Default rendering context (default: null)
     */
    public function __construct($context = null)
    {
        if ($context !== null) {
            $this->stack = array($context);
        }
    }

    /**
     * Push a new Context frame onto the stack.
     *
     * @param mixed $value Object or array to use for context
     */
    public function push($value)
    {
        array_push($this->stack, $value);
    }

    /**
     * Push a new Context frame onto the block context stack.
     *
     * @param mixed $value Object or array to use for block context
     */
    public function pushBlockContext($value)
    {
        array_push($this->blockStack, $value);
    }

    /**
     * Pop the last Context frame from the stack.
     *
     * @return mixed Last Context frame (object or array)
     */
    public function pop()
    {
        return array_pop($this->stack);
    }

    /**
     * Pop the last block Context frame from the stack.
     *
     * @return mixed Last block Context frame (object or array)
     */
    public function popBlockContext()
    {
        return array_pop($this->blockStack);
    }

    /**
     * Get the last Context frame.
     *
     * @return mixed Last Context frame (object or array)
     */
    public function last()
    {
        return end($this->stack);
    }

    /**
     * Find a variable in the Context stack.
     *
     * Starting with the last Context frame (the context of the innermost section), and working back to the top-level
     * rendering context, look for a variable with the given name:
     *
     *  * If the Context frame is an associative array which contains the key $id, returns the value of that element.
     *  * If the Context frame is an object, this will check first for a public method, then a public property named
     *    $id. Failing both of these, it will try `__isset` and `__get` magic methods.
     *  * If a value named $id is not found in any Context frame, returns an empty string.
     *
     * @param string $id Variable name
     *
     * @return mixed Variable value, or '' if not found
     */
    public function find($id)
    {
        //CUSTOM
        $value = $this->findVariants($id);
        
        if ($value !== false) {
            return $value;
        }
        //END CUSTOM
        
        return $this->findVariableInStack($id, $this->stack);
    }

    /**
     * Find a 'dot notation' variable in the Context stack.
     *
     * Note that dot notation traversal bubbles through scope differently than the regular find method. After finding
     * the initial chunk of the dotted name, each subsequent chunk is searched for only within the value of the previous
     * result. For example, given the following context stack:
     *
     *     $data = array(
     *         'name' => 'Fred',
     *         'child' => array(
     *             'name' => 'Bob'
     *         ),
     *     );
     *
     * ... and the Mustache following template:
     *
     *     {{ child.name }}
     *
     * ... the `name` value is only searched for within the `child` value of the global Context, not within parent
     * Context frames.
     *
     * @param string $id Dotted variable selector
     *
     * @return mixed Variable value, or '' if not found
     */
    public function findDot($id)
    {
        //CUSTOM
        $value = $this->findVariants($id);
        
        if ($value !== false) {
            return $value;
        }
        //END CUSTOM
        
        $chunks = explode('.', $id);
        $first  = array_shift($chunks);
        $value  = $this->findVariableInStack($first, $this->stack);

        foreach ($chunks as $chunk) {
            if ($value === '') {
                return $value;
            }

            $value = $this->findVariableInStack($chunk, array($value));
        }

        return $value;
    }

    /**
     * Find an 'anchored dot notation' variable in the Context stack.
     *
     * This is the same as findDot(), except it looks in the top of the context
     * stack for the first value, rather than searching the whole context stack
     * and starting from there.
     *
     * @see Mustache_Context::findDot
     *
     * @throws Mustache_Exception_InvalidArgumentException if given an invalid anchored dot $id.
     *
     * @param string $id Dotted variable selector
     *
     * @return mixed Variable value, or '' if not found
     */
    public function findAnchoredDot($id)
    {
        //CUSTOM
        $value = $this->findVariants($id);
        
        if ($value !== false) {
            return $value;
        }
        //END CUSTOM
        
        $chunks = explode('.', $id);
        $first  = array_shift($chunks);
        if ($first !== '') {
            throw new Exception(sprintf('Unexpected id for findAnchoredDot: %s', $id));
        }

        $value  = $this->last();

        foreach ($chunks as $chunk) {
            if ($value === '') {
                return $value;
            }

            $value = $this->findVariableInStack($chunk, array($value));
        }

        return $value;
    }
    
    /**
     * Finds only exclusive to literal values
     * as opposed to helpers
     *
     * @param string $id    the varaiable name
     * @param array  $stack the prescribed stack
     *
     * @return string
     */
    public function findLiteral($id, array $stack = null)
    {
        if (is_null($stack)) {
            $stack = $this->stack;
        }
        
        //if it starts with a ./
        if (strpos($id, './') === 0) {
            //we already understand it means get the literal value
            $id = substr($id, 2);
        }
        
        //we can safely use findVariableInStack because
        //we are suggesting the stack as opposed to the
        //original stack with helpers
        $chunks = explode('.', $id);
        $first  = array_shift($chunks);
        $value  = $this->findVariableInStack($first, $stack, true);

        foreach ($chunks as $chunk) {
            if ($value === '') {
                return $value;
            }

            $value = $this->findVariableInStack($chunk, array($value), true);
        }

        return $value;
    }
    
    /**
     * Finds only exclusive to helpers
     * as opposed to literal values
     *
     * @param string $id    the varaiable name
     * @param array  $stack the prescribed stack
     *
     * @return string
     */
    public function findHelper($id, array $stack = null)
    {
        if (is_null($stack)) {
            $stack = $this->stack;
        }
        
        $end = strpos($id, ' ');
        $method = $id;
        if ($end !== false) {
            $method = substr($id, 0, $end);
        }
        
        for ($i = count($stack) - 1; $i >= 0; $i--) {
            $frame = &$stack[$i];
            
            if (!is_object($frame)) {
                continue;
            }
            
            if (method_exists($frame, $method)) {
                return $frame->$id();
            }
            
            if (isset($frame->$method)) {
                return $frame->$id;
            }
        }
        
        return '';
    }
    
    /**
     * Finds only exclusive to parents
     * then finds the literal value as opposed to helpers
     *
     * @param string $id    the varaiable name
     * @param array  $stack the prescribed stack
     *
     * @return string
     */
    public function findParent($id, array $stack = null)
    {
        if (is_null($stack)) {
            $stack = $this->stack;
        }
        
        //loop through the stack
        for ($i = count($stack) - 1; $i >= 0; $i--) {
            $frame = &$stack[$i];
            
            //if frame is array access and exists
            if ($frame instanceof \ArrayAccess && isset($frame[$id])) {
                //we got it
                return $frame[$id];
            }
            
            //if frame is not an array
            if (!is_array($frame)) {
                //skip it
                continue;
            }
            
            //if it exists (yes even if it has a ../)
            //this is usually the end of this method.
            if (array_key_exists($id, $frame)) {
                //we got it
                return $frame[$id];
            }
            
            //if it starts with a ../
            if (strpos($id, '../') === 0) {
                //remove it
                $id = substr($id, 3);
                //and keep traversing
                continue;
            }
            
            //if there's a .
            if (strpos($id, '.') !== 0 && strpos($id, '.') !== false) {
                //then we still got it
                return $this->findLiteral($id, array($frame));
            }
            
            //keep traversing
        }
        
        //um we didn't find it :(
        return '';
    }
    
    public function findVariants($id)
    {
        //if there are spaces
        if (strpos($id, ' ') !== false) {
            return $this->findHelper($id);
        }
        
        //if we are requesting for the parent
        if (strpos($id, '../') === 0) {
            return $this->findParent($id);
        }
        
        //if literal value
        //see {{./noop}} handlebars.js
        if (strpos($id, './') === 0
            || (strpos($id, '.') !== false
            && strpos($id, '.') !== 0)
        ) {
            return $this->findLiteral($id);
        }
        
        return false;
    }

    /**
     * Find an argument in the block context stack.
     *
     * @param string $id
     *
     * @return mixed Variable value, or '' if not found.
     */
    public function findInBlock($id)
    {
        foreach ($this->blockStack as $context) {
            if (array_key_exists($id, $context)) {
                return $context[$id];
            }
        }

        return '';
    }

    /**
     * Helper function to find a variable in the Context stack.
     *
     * @see Mustache_Context::find
     *
     * @param string $id    Variable name
     * @param array  $stack Context stack
     *
     * @return mixed Variable value, or '' if not found
     */
    private function findVariableInStack($id, array $stack, $literal = false)
    {
        //CUSTOM
        //if id is [0]
        if (preg_match('/^\[[^\]]+\]$/', $id)) {
            $id = substr($id, 1, -1);
        }
        //END CUSTOM
        
        for ($i = count($stack) - 1; $i >= 0; $i--) {
            $frame = &$stack[$i];

            switch (gettype($frame)) {
                case 'object':
                    //if looking for the literal value
                    if ($literal) {
                        if ($frame instanceof \ArrayAccess && isset($frame[$id])) {
                            return $frame[$id];
                        }
                        
                        break;
                    }
                    
                    if (!($frame instanceof Closure)) {
                        // Note that is_callable() *will not work here*
                        // See https://github.com/bobthecow/mustache.php/wiki/Magic-Methods
                        if (method_exists($frame, $id)) {
                            return $frame->$id();
                        }

                        if (isset($frame->$id)) {
                            return $frame->$id;
                        }

                        if ($frame instanceof \ArrayAccess && isset($frame[$id])) {
                            return $frame[$id];
                        }
                    }
                    break;

                case 'array':
                    if (array_key_exists($id, $frame)) {
                        return $frame[$id];
                    }
                    break;
            }
        }
        
        //CUSTOM
        //if is a number
        if (is_numeric($id)) {
            return (float) $id;
        }
        
        //length is used in JS and is
        //put here for compatibility
        if ($id === 'length' && isset($stack[0])) {
            if (is_array($stack[0])) {
                return count($stack[0]);
            }
            
            if (is_string($stack[0])) {
                return strlen($stack[0]);
            }
            
            if (is_numeric($stack[0])) {
                return (float) $stack[0];
            }
            
            return 0;
        }
        //END CUSTOM
        
        return '';
    }
}
