<?php
declare(strict_types=1);

namespace MattyG\Handlebars\Helper;

class WithHelper
{
    /**
     * @param mixed $value
     * @param array $options
     * @return string
     */
    public function __invoke($value, $options)
    {
        if (!is_array($value)) {
            $value = array('this' => $value);
        } else {
            $value['this'] = $value;
        }

        return $options['fn']($value);
    }
}
