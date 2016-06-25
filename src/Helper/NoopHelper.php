<?php
declare(strict_types=1);

namespace MattyG\Handlebars\Helper;

class NoopHelper
{
    public function __invoke()
    {
        $args = func_get_args();
        $options = array_pop($args);
        $context = null;

        if (count($args)) {
            $context = array_merge($args[0], $options['hash']);
        } else if (!empty($options['hash'])) {
            $context = $options['hash'];
        }

        return $options['fn']($context);
    }
}
