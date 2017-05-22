<?php
declare(strict_types=1);

namespace MattyG\Handlebars\Helper;

class ConcatHelper
{
    /**
     * @param ...$args
     */
    public function __invoke(...$args)
    {
        $options = array_pop($args);
        return implode("", $args);
    }
}
