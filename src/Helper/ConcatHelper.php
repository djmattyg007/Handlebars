<?php
declare(strict_types=1);

namespace MattyG\Handlebars\Helper;

class ConcatHelper
{
    public function __invoke()
    {
        $args = func_get_args();
        $options = array_pop($args);
        return array_reduce($args, function($carry, $item) { return $carry . $item; }, "");
    }
}
