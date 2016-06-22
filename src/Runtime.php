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
class Runtime extends \Eden\Core\Base
{
    /**
     * A raw list of partials
     *
     * @var array
     */
    protected static $partials = array();
    
    /**
     * A raw list of helpers
     *
     * @var array
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
     * TODO: Look at $bind parameter
     * @param string $name The name of the helper
     * @return function|null
     */
    public static function getHelper(string $name, Data $bind = null)
    {
        if (isset(self::$helpers[$name])) {
            return self::$helpers[$name];
        }
        
        return null;
    }

    /**
     * Returns all the registered helpers
     *
     * TODO: Look at $bind parameter
     * @return array
     */
    public static function getHelpers(Data $bind = null) : array
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
     * @param string $name The name of the helper
     * @return string|null
     */
    public static function getPartial(string $name)
    {
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
    public static function getPartials() : array
    {
        return self::$partials;
    }

    /**
     * The famous register helper matching the Handlebars API
     *
     * @param string $name The name of the helper
     * @param function $helper The helper handler
     */
    public static function registerHelper(string $name, $helper)
    {
        self::$helpers[$name] = $helper;
    }

    /**
     * Delays registering partials to the engine
     * because there is no add partial method...
     *
     * @param string $name The name of the helper
     * @param string $partial The helper handler
     */
    public static function registerPartial(string $name, string $partial)
    {
        self::$partials[$name] = $partial;
    }

    /**
     * The opposite of registerHelper
     *
     * @param string $name the helper name
     */
    public static function unregisterHelper(string $name)
    {
        if (isset(self::$helpers[$name])) {
            unset(self::$helpers[$name]);
        }
    }

    /**
     * The opposite of registerPartial
     *
     * @param string $name the partial name
     */
    public static function unregisterPartial(string $name)
    {
        if (isset(self::$partials[$name])) {
            unset(self::$partials[$name]);
        }
    }
}
