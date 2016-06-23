<?php
declare(strict_types=1);

namespace Eden\Handlebars\Helper;

class IfHelper
{
    public function __invoke($value, $options)
    {
        if (!!$value) {
            return $options["fn"]();
        }

        return $options["inverse"]();
    }
}
