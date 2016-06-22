<?php //-->
return array(
    'if' => function ($value, $options) {
        $args = func_get_args();
        $options = array_pop($args);

        if (!!$value) {
            return $options['fn']();
        }

        return $options['inverse']();
    },

    'unless' => function ($value, $options) {
        $args = func_get_args();
        $options = array_pop($args);

        if (!!$value) {
            return $options['inverse']();
        }

        return $options['fn']();
    },

    'with' => function ($value, $options) {
        if (!is_array($value)) {
            $value = array('this' => $value);
        } else {
            $value['this'] = $value;
        }

        return $options['fn']($value);
    },

    'each' => function ($object, $options) {
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

            $keyName = trim($keyName);
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
    },

    'tokenize->' => function ($name) {
        //get args
        $args = func_get_args();

        //get the name
        //it will be like 'something'
        //or $data->find('something')
        $original = $name = array_shift($args);

        //we need the options
        $options = array_pop($args);

        //if it's a data lookup
        if (strpos($name, '$data->find(') === 0) {
            //this is not what we really want
            $name = substr($name, 12, -1);
        }

        //if it has quotes
        if (substr($name, 0, 1) === "'" && substr($name, -1) === "'") {
            //remove it
            $name = substr($name, 1, -1);
        }

        //get the partial
        $partial = \Eden\Handlebars\Runtime::getPartial($name);

        //but if the partial is null
        if (is_null($partial)) {
            //name is really the partial
            $partial = $name;
        }

        //if there are still arguments
        $scope = '';
        if (count($args)) {
            $scope = '\r\t\1' . $args[0] . ', ';
        }

        //form hash
        $hash = array();
        foreach ($options['hash'] as $key => $value) {
            $hash[$key] = sprintf('\'%s\' => %s', $key, $value);
        }

        if (empty($hash)) {
            $hash = '';
        } else {
            $hash = '\r\t\1\1\1' . implode(', \r\t\1\1\1', $hash) . '\r\t\1\1';
        }

        $level = $options['offset'];

        $layout = str_replace(
            array('\r', '\t', '\1'),
            array("\n",
                str_repeat('    ', $options['offset']),
                str_repeat('    ', 1)
            ),
            '\r\t$buffer .= $helper[\'noop\']('
            . $scope
            . '\r\t\1array('
            . '\r\t\1\1\'name\' => \'noop\','
            . '\r\t\1\1\'hash\' => array('.$hash.'),'
            . '\r\t\1\1\'fn\' => function($context = null) use ($noop, $data, &$helper) {'
            . '\r\t\1\1\1if (is_array($context)) {'
            . '\r\t\1\1\1\1$data->push($context);'
            . '\r\t\1\1\1}'
            . '\r\r\t\1\1\1$buffer = \'\';'
            . '%s'
            . '\r\t\1\1\1if (is_array($context)) {'
            . '\r\t\1\1\1\1$data->pop();'
            . '\r\t\1\1\1}'
            . '\r\r\t\1\1\1return $buffer;'
            . '\r\t\1\1},'
            . '\r\t\1\1\'inverse\' => $noop'
            . '\r\t\1)'
            . '\r\t);\r'
        );

        $code = \Eden\Handlebars\Compiler::i($options['handlebars'], $partial)
            ->setOffset($options['offset'] + 3)
            ->compile(false);

        return sprintf($layout, $code);
    },

    'noop' => function () {
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
);
