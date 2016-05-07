<?php //-->
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
class Runtime extends Base
{
    /**
     * @var array $partials A raw list of partials
     */
    protected static $partials = array();
    
    /**
     * @var array $helpers A raw list of helpers
     */
    protected static $helpers = array();

    /**
     * Resets the helpers and partials
     */
    public static function flush()
    {
         self::$partials = array();
         self::$helpers = array();
    }

    /**
     * Returns a specific helper
     *
     * @param *string $name The name of the helper
     *
     * @return function|null
     */
    public static function getHelper($name, Data $bind = null)
    {
        //Argument 1 must be a string
        Argument::i()->test(1, 'string');
        
        if (isset(self::$helpers[$name])) {
            return self::$helpers[$name];
        }
        
        return null;
    }

    /**
     * Returns all the registered helpers
     *
     * @return array
     */
    public static function getHelpers(Data $bind = null)
    {
        if (is_null($bind)) {
            return self::$helpers;
        }
        
        $helpers = array();
        
        foreach (self::$helpers as $name => $helper) {
            $helpers[$name] = $helper->bindTo($bind, '\\Eden\\Handlebars\\Data');
        }
        
        return $helpers;
    }

    /**
     * Returns a specific partial
     *
     * @param *string $name The name of the helper
     *
     * @return string|null
     */
    public static function getPartial($name)
    {
        //Argument 1 must be a string
        Argument::i()->test(1, 'string');
        
        if (isset(self::$partials[$name])) {
            return self::$partials[$name];
        }
        
        return null;
    }

    /**
     * Returns all the registered partials
     *
     * @return array
     */
    public static function getPartials()
    {
        return self::$partials;
    }

    /**
     * Turns off eden argument handlers
     */
    public static function optimize()
    {
        Argument::i()->stop();
    }

    /**
     * The famous register helper matching the Handlebars API
     *
     * @param *string   $name   The name of the helper
     * @param *function $helper The helper handler
     */
    public static function registerHelper($name, $helper)
    {
        Argument::i()
            //Argument 1 must be a string
            ->test(1, 'string')
            //Argument 2 must be a Closure of some kind
            ->test(2, 'Closure');
        
        self::$helpers[$name] = $helper;
    }

    /**
     * Delays registering partials to the engine
     * because there is no add partial method...
     *
     * @param *string $name    The name of the helper
     * @param *string $partial The helper handler
     */
    public static function registerPartial($name, $partial)
    {
        Argument::i()
            //Argument 1 must be a string
            ->test(1, 'string')
            //Argument 2 must be a string
            ->test(2, 'string');

        self::$partials[$name] = $partial;
    }

    /**
     * The opposite of registerHelper
     *
     * @param *string $name the helper name
     */
    public static function unregisterHelper($name)
    {
        //Argument 1 must be a string
        Argument::i()->test(1, 'string');

        if (isset(self::$helpers[$name])) {
            unset(self::$helpers[$name]);
        }
    }

    /**
     * The opposite of registerPartial
     *
     * @param *string $name the partial name
     */
    public static function unregisterPartial($name)
    {
        //Argument 1 must be a string
        Argument::i()->test(1, 'string');

        if (isset(self::$partials[$name])) {
            unset(self::$partials[$name]);
        }
    }
}
