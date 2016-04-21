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
 * I need to hijack the cache method to replace the static
 * declaration of Mustache_Template to my custom Template
 *
 * @vendor   Eden
 * @package  Handlebars
 * @author   Christian Blanquera <cblanquera@openovate.com>
 * @standard PSR-2
 */
class FilesystemCache extends \Mustache_Cache_FilesystemCache
{
    /**
     * Loads the compiled Mustache Template class without caching.
     *
     * @param string $key
     * @param string $value
     */
    public function cache($key, $value)
    {
        //CUSTOM
		$value = str_replace(
            'extends Mustache_Template',
            'extends \\Eden\\Handlebars\\Template',
            $value
        );
        //END CUSTOM

        parent::cache($key, $value);
    }
}
