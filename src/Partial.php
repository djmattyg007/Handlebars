<?php //-->
/**
 * This file is part of the Eden package.
 * (c) 2014-2016 Openovate Labs
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

namespace Eden\Handlebars;

/**
 * This is for covering the comilped version to override
 * renderInternal to generate from a helper instead
 *
 * @vendor   Eden
 * @package  Handlebars
 * @author   Christian Blanquera <cblanquera@openovate.com>
 * @standard PSR-2
 */
class Partial extends Template
{
    protected $args = array();
    protected $helper = null;
    
    /**
     * Mustache Template constructor.
     *
     * @param Mustache_Engine $mustache
     */
    public function __construct(\Mustache_Engine $mustache, $helper, array $args = array())
    {
        $this->mustache = $mustache;
        $this->helper = $helper;
        $this->args = $args;
    }
    
    /**
     * Internal rendering method implemented by Mustache Template concrete subclasses.
     *
     * This is where the magic happens :)
     *
     * NOTE: This method is not part of the Mustache.php public API.
     *
     * @param Mustache_Context $context
     * @param string           $indent  (default: '')
     *
     * @return string Rendered template
     */
    public function renderInternal(\Mustache_Context $context, $indent = '')
    {
        $bound = $this->helper->bindTo($context, get_class($context));
        return call_user_func_array($bound, $this->args);
    }
}
