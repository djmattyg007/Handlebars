<?php
declare(strict_types=1);

namespace MattyG\Handlebars\Helper;

class EachHelper
{
    public function __invoke($object, $options)
    {
        $args = func_get_args();
        $options = array_pop($args);

        if (is_scalar($object) || !$object) {
            return $options['inverse']();
        }

        //test foreach
        $keyName = null;
        $valueName = null;
        //see handlebars.js {{#each array as |value, key|}}
        if (strpos($options['args'], ' as |') !== false
            && substr_count($options['args'], '|') === 2
        ) {
            list($tmp, $valueName) = explode('|', $options['args']);

            if (strpos($valueName, ',') !== false) {
                list($valueName, $keyName) = explode(',', trim($valueName));
            }

            $keyName = trim((string) $keyName);
            $valueName = trim($valueName);
        }

        $buffer = array();
        $object = (array) $object;

        //get last
        end($object);
        $last = key($object);

        //get first
        reset($object);
        $first = key($object);

        foreach ($object as $key => $value) {
            if (!is_array($value)) {
                $value = array('this' => $value);
            } else {
                $value['this'] = $value;
            }

            if ($valueName) {
                $value[$valueName] = $value['this'];
            }

            if ($keyName) {
                $value[$keyName] = $key;
            }

            $value['@index'] = $key;
            $value['@key'] = $key;
            $value['@first'] = $first;
            $value['@last'] = $last;

            $buffer[] = $options['fn']($value);
        }

        return implode('', $buffer);
    }
}
