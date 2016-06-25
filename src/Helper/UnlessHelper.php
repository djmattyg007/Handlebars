<?php
declare(strict_types=1);

namespace MattyG\Handlebars\Helper;

class UnlessHelper
{
    public function __invoke($value, $options)
    {
        if (!!$value) {
            return $options["inverse"]();
        }

        return $options["fn"]();
    }
}
