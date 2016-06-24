<?php
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
 * Transforms Handlebars Templates to PHP equivilent
 *
 * @vendor   Eden
 * @package  handlebars
 * @author   Christian Blanquera <cblanquera@openovate.com>
 * @standard PSR-2
 */
class Compiler
{
    const BLOCK_TEXT_LINE = '\r\t$buffer .= \'%s\'.\n;';
    const BLOCK_TEXT_LAST = '\r\t$buffer .= \'%s\';';

    const BLOCK_ESCAPE_VALUE = '\r\t$buffer .= $data->find(\'%s\');\r';
    const BLOCK_VARIABLE_VALUE = '\r\t$buffer .= htmlspecialchars($data->find(\'%s\'), ENT_COMPAT, \'UTF-8\');\r';

    const BLOCK_ESCAPE_HELPER_OPEN = '\r\t$buffer .= $helper[\'%s\'](';
    const BLOCK_ESCAPE_HELPER_CLOSE = '\r\t);\r';

    const BLOCK_VARIABLE_HELPER_OPEN = '\r\t$buffer .= htmlspecialchars($helper[\'%s\'](';
    const BLOCK_VARIABLE_HELPER_CLOSE = '\r\t), ENT_COMPAT, \'UTF-8\');\r';

    const BLOCK_ARGUMENT_VALUE = '$data->find(\'%s\')';

    const BLOCK_OPTIONS_OPEN = 'array(';
    const BLOCK_OPTIONS_CLOSE = '\r\t)';

    const BLOCK_OPTIONS_FN_OPEN = '\r\t\'fn\' => function($context = null) use ($noop, $data, &$helper) {';
    const BLOCK_OPTIONS_FN_BODY_1 = '\r\t\1if (is_array($context)) {';
    const BLOCK_OPTIONS_FN_BODY_2 = '\r\t\1\1$data->push($context);';
    const BLOCK_OPTIONS_FN_BODY_3 = '\r\t\1}';
    const BLOCK_OPTIONS_FN_BODY_4 = '\r\r\t\1$buffer = \'\';';
    const BLOCK_OPTIONS_FN_BODY_5 = '\r\r\t\1if (is_array($context)) {';
    const BLOCK_OPTIONS_FN_BODY_6 = '\r\t\1\1$data->pop();';
    const BLOCK_OPTIONS_FN_BODY_7 = '\r\t\1}';
    const BLOCK_OPTIONS_FN_CLOSE = '\r\r\t\1return $buffer;\r\t},\r';

    const BLOCK_OPTIONS_INVERSE_OPEN = '\r\t\'inverse\' => function($context = null) use ($noop, $data, &$helper) {';
    const BLOCK_OPTIONS_INVERSE_BODY_1 = '\r\t\1if (is_array($context)) {';
    const BLOCK_OPTIONS_INVERSE_BODY_2 = '\r\t\1\1$data->push($context);';
    const BLOCK_OPTIONS_INVERSE_BODY_3 = '\r\t\1}';
    const BLOCK_OPTIONS_INVERSE_BODY_4 = '\r\r\t\1$buffer = \'\';';
    const BLOCK_OPTIONS_INVERSE_BODY_5 = '\r\r\t\1if (is_array($context)) {';
    const BLOCK_OPTIONS_INVERSE_BODY_6 = '\r\t\1\1$data->pop();';
    const BLOCK_OPTIONS_INVERSE_BODY_7 = '\r\t\1}';
    const BLOCK_OPTIONS_INVERSE_CLOSE = '\r\r\t\1return $buffer;\r\t}\r';

    const BLOCK_OPTIONS_FN_EMPTY = '\r\t\'fn\' => $noop,';
    const BLOCK_OPTIONS_INVERSE_EMPTY = '\r\t\'inverse\' => $noop';
    const BLOCK_OPTIONS_NAME = '\r\t\'name\' => \'%s\',';
    const BLOCK_OPTIONS_ARGS = '\r\t\'args\' => \'%s\',';
    const BLOCK_OPTIONS_HASH = '\r\t\'hash\' => array(%s),';
    const BLOCK_OPTIONS_HASH_KEY_VALUE = '\'%s\' => %s';

    const LAST_OPEN = ' LAST ';

    const ERROR_AND = ' AND ';
    const ERROR_LINE = '"%s" on line %s';
    const ERROR_MISSING_CLOSING = 'Missing closing tags for: %s';
    const ERROR_UNKNOWN_END = 'Unknown close tag: "%s" on line %s';

    /**
     * @var string
     */
    protected static $layout = null;

    /**
     * @var Index
     */
    protected $handlebars;

    /**
     * @var Tokenizer
     */
    protected $tokenizer;

    /**
     * @var int
     */
    protected $offset = 1;

    /**
     * Just load the source template
     *
     * @param Index $handlebars
     * @param Tokenizer $tokenizer
     */
    public function __construct(Index $handlebars, Tokenizer $tokenizer)
    {
        $this->handlebars = $handlebars;
        $this->tokenizer = $tokenizer;

        if (is_null(self::$layout)) {
            self::$layout = file_get_contents(__DIR__ . '/layout.template');
        }
    }

    /**
     * Transform the template to code
     * that can be used independently
     *
     * @param bool $layout Whether to use the layout or raw code
     * @return string
     */
    public function compile(bool $layout = true)
    {
        $buffer = '';
        $open = array();

        $this->tokenizer->tokenize(function ($node) use (&$buffer, &$open) {
            switch ($node['type']) {
                case Tokenizer::TYPE_TEXT:
                    $buffer .= $this->generateText($node, $open);
                    break;
                case Tokenizer::TYPE_VARIABLE_ESCAPE:
                    $buffer .= $this->generateEscape($node, $open);
                    break;
                case Tokenizer::TYPE_VARIABLE_UNESCAPE:
                    $buffer .= $this->generateVariable($node, $open);
                    break;
                case Tokenizer::TYPE_SECTION_OPEN:
                    $buffer .= $this->generateOpen($node, $open);
                    break;
                case Tokenizer::TYPE_SECTION_CLOSE:
                    $buffer .= $this->generateClose($node, $open);
                    break;
            }
        });
        $buffer .= "\n";

        //START: This is more to help troubleshooting
        if (count($open)) {
            foreach ($open as $i => $item) {
                $open[$i] = sprintf(self::ERROR_LINE, $item['value'], $item['line']);
            }

            throw new Exception(sprintf(self::ERROR_MISSING_CLOSING, implode(self::ERROR_AND, $open)));
        }
        //END: This is more to help troubleshooting

        if (!$layout) {
            return $buffer;
        }

        return sprintf(self::$layout, $buffer);
    }

    /**
     * Returns a code snippet
     *
     * TODO: Work out what "the tabbing" is
     * @param int $offset This is to preset the tabbing when generating the code
     * @return Compiler
     */
    public function setOffset(int $offset) : Compiler
    {
        $this->offset = $offset;
        return $this;
    }

    /**
     * Partially renders the text tokens
     *
     * @param array $node
     * @param array $open
     * @return string
     */
    protected function generateText(array $node) : string
    {
        $buffer = '';

        $value = explode("\n", $node['value']);
        $last = count($value) - 1;

        foreach ($value as $i => $line) {
            $line = str_replace("'", '\\\'', $line);

            if ($i === $last) {
                $buffer .= $this->prettyPrint(sprintf(self::BLOCK_TEXT_LAST, $line));
                continue;
            }

            $buffer .= $this->prettyPrint(sprintf(self::BLOCK_TEXT_LINE, $line));
        }

        return $buffer;
    }

    /**
     * Partially renders the unescaped variable tokens
     *
     * @param array $node
     * @param array $open
     * @return string
     */
    protected function generateVariable(array $node, array &$open) : string
    {
        $node['value'] = trim($node['value']);

        //look out for else
        if ($node['value'] === 'else') {
            $open[$this->findSection($open)]['else'] = true;

            return $this->prettyPrint(self::BLOCK_OPTIONS_FN_BODY_5, -1)
                . $this->prettyPrint(self::BLOCK_OPTIONS_FN_BODY_6)
                . $this->prettyPrint(self::BLOCK_OPTIONS_FN_BODY_7)
                . $this->prettyPrint(self::BLOCK_OPTIONS_FN_CLOSE)
                . $this->prettyPrint(self::BLOCK_OPTIONS_INVERSE_OPEN)
                . $this->prettyPrint(self::BLOCK_OPTIONS_INVERSE_BODY_1)
                . $this->prettyPrint(self::BLOCK_OPTIONS_INVERSE_BODY_2)
                . $this->prettyPrint(self::BLOCK_OPTIONS_INVERSE_BODY_3)
                . $this->prettyPrint(self::BLOCK_OPTIONS_INVERSE_BODY_4, 0, 1);
        }

        //lookout for tokenizer
        $tokenized = $this->tokenize($node);
        if ($tokenized) {
            return $tokenized;
        }

        list($name, $args, $hash) = $this->parseArguments($node['value']);

        //if it's a helper
        if (Runtime::getHelper($name)) {
            //form hash
            foreach ($hash as $key => $value) {
                $hash[$key] = sprintf(self::BLOCK_OPTIONS_HASH_KEY_VALUE, $key, $value);
            }

            $args[] = $this->prettyPrint(self::BLOCK_OPTIONS_OPEN, 0, 2)
                . $this->prettyPrint(sprintf(self::BLOCK_OPTIONS_NAME, $name))
                . $this->prettyPrint(sprintf(self::BLOCK_OPTIONS_ARGS, str_replace("'", '\\\'', $node['value'])))
                . $this->prettyPrint(sprintf(self::BLOCK_OPTIONS_HASH, implode(', \r\t', $hash)))
                . $this->prettyPrint(self::BLOCK_OPTIONS_FN_EMPTY)
                . $this->prettyPrint(self::BLOCK_OPTIONS_INVERSE_EMPTY)
                . $this->prettyPrint(self::BLOCK_OPTIONS_CLOSE, -1);

            return $this->prettyPrint(sprintf(self::BLOCK_VARIABLE_HELPER_OPEN, $name), -1)
                . $this->prettyPrint('\r\t' . implode(', \r\t', $args), 1, -1)
                . $this->prettyPrint(self::BLOCK_VARIABLE_HELPER_CLOSE);
        }

        //it's a value ?
        $value = str_replace(array('[', ']', '(', ')'), '', $node['value']);
        $value = str_replace("'", '\\\'', $value);
        return $this->prettyPrint(sprintf(self::BLOCK_VARIABLE_VALUE, $value));
    }

    /**
     * Partially renders the escaped variable tokens
     *
     * @param array $node
     * @param array $open
     * @return string
     */
    protected function generateEscape(array $node, array &$open) : string
    {
        $node['value'] = trim($node['value']);

        //lookout for tokenizer
        $tokenized = $this->tokenize($node);
        if ($tokenized) {
            return $tokenized;
        }

        list($name, $args, $hash) = $this->parseArguments($node['value']);

        //if it's a helper
        if (Runtime::getHelper($name)) {
            //form hash
            foreach ($hash as $key => $value) {
                $hash[$key] = sprintf(self::BLOCK_OPTIONS_HASH_KEY_VALUE, $key, $value);
            }

            $args[] = $this->prettyPrint(self::BLOCK_OPTIONS_OPEN, 0, 2)
                . $this->prettyPrint(sprintf(self::BLOCK_OPTIONS_NAME, $name))
                . $this->prettyPrint(sprintf(self::BLOCK_OPTIONS_ARGS, str_replace("'", '\\\'', $node['value'])))
                . $this->prettyPrint(sprintf(self::BLOCK_OPTIONS_HASH, implode(', \r\t', $hash)))
                . $this->prettyPrint(self::BLOCK_OPTIONS_FN_EMPTY)
                . $this->prettyPrint(self::BLOCK_OPTIONS_INVERSE_EMPTY)
                . $this->prettyPrint(self::BLOCK_OPTIONS_CLOSE, -1);

            return $this->prettyPrint(sprintf(self::BLOCK_ESCAPE_HELPER_OPEN, $name), -1)
                . $this->prettyPrint('\r\t' . implode(', \r\t', $args), 1, -1)
                . $this->prettyPrint(self::BLOCK_ESCAPE_HELPER_CLOSE);
        }

        //it's a value ?
        $value = str_replace(array('[', ']', '(', ')'), '', $node['value']);
        $value = str_replace("'", '\\\'', $value);
        return $this->prettyPrint(sprintf(self::BLOCK_ESCAPE_VALUE, $value));
    }

    /**
     * Partially renders the section open tokens
     *
     * @param array $node
     * @param array $open
     * @return string
     */
    protected function generateOpen(array $node, array &$open) : string
    {
        $node['value'] = trim($node['value']);

        //push in the node, we are going to need this to close
        $open[] = $node;

        list($name, $args, $hash) = $this->parseArguments($node['value']);

        //if it's a value
        if (is_null(Runtime::getHelper($name))) {
            //run each
            $node['value'] = 'each '.$node['value'];
            list($name, $args, $hash) = $this->parseArguments($node['value']);
        }

        //it's a helper
        //form hash
        foreach ($hash as $key => $value) {
            $hash[$key] = sprintf(self::BLOCK_OPTIONS_HASH_KEY_VALUE, $key, $value);
        }

        $args[] = $this->prettyPrint(self::BLOCK_OPTIONS_OPEN, 0, 2)
            . $this->prettyPrint(sprintf(self::BLOCK_OPTIONS_NAME, $name))
            . $this->prettyPrint(sprintf(self::BLOCK_OPTIONS_ARGS, str_replace("'", '\\\'', $node['value'])))
            . $this->prettyPrint(sprintf(self::BLOCK_OPTIONS_HASH, implode(', \r\t', $hash)))
            . $this->prettyPrint(self::BLOCK_OPTIONS_FN_OPEN)
            . $this->prettyPrint(self::BLOCK_OPTIONS_FN_BODY_1)
            . $this->prettyPrint(self::BLOCK_OPTIONS_FN_BODY_2)
            . $this->prettyPrint(self::BLOCK_OPTIONS_FN_BODY_3)
            . $this->prettyPrint(self::BLOCK_OPTIONS_FN_BODY_4);

        return $this->prettyPrint(sprintf(self::BLOCK_ESCAPE_HELPER_OPEN, $name), -2)
            . $this->prettyPrint('\r\t' . implode(', \r\t', $args), 1, 2);
    }

    /**
     * Partially renders the section close tokens
     *
     * @param array $node
     * @param array $open
     * @return string
     */
    protected function generateClose(array $node, array &$open) : string
    {
        $node['value'] = trim($node['value']);

        //START: This is more to help troubleshooting
        if ($this->findSection($open, $node['value']) === false) {
            throw new Exception(sprintf(self::ERROR_UNKNOWN_END, $node['value'], $node['line']));
        }
        //END: This is more to help troubleshooting

        $buffer = '';

        $i = $this->findSection($open);

        if (!isset($open[$i]['else'])) {
            $buffer .= $this->prettyPrint(self::BLOCK_OPTIONS_FN_BODY_5, -1);
            $buffer .= $this->prettyPrint(self::BLOCK_OPTIONS_FN_BODY_6);
            $buffer .= $this->prettyPrint(self::BLOCK_OPTIONS_FN_BODY_7);
            $buffer .= $this->prettyPrint(self::BLOCK_OPTIONS_FN_CLOSE);
            $buffer .= $this->prettyPrint(self::BLOCK_OPTIONS_INVERSE_EMPTY);
        } else {
            $buffer .= $this->prettyPrint(self::BLOCK_OPTIONS_INVERSE_BODY_5);
            $buffer .= $this->prettyPrint(self::BLOCK_OPTIONS_INVERSE_BODY_6);
            $buffer .= $this->prettyPrint(self::BLOCK_OPTIONS_INVERSE_BODY_7);
            $buffer .= $this->prettyPrint(self::BLOCK_OPTIONS_INVERSE_CLOSE, -1);
        }

        unset($open[$i]);

        $buffer .= $this->prettyPrint(self::BLOCK_OPTIONS_CLOSE, -1);
        $buffer .= $this->prettyPrint(self::BLOCK_ESCAPE_HELPER_CLOSE, -1);

        return $buffer;
    }

    /**
     * Generates partials to add to the layout
     * This is a placeholder incase we want to add in the future
     *
     * @return string
     */
    protected function generatePartials() : string
    {
        $partials = $this->handlebars->getPartials();
        
        foreach ($partials as $name => $partial) {
            $partials[$name] = sprintf(
                self::BLOCK_OPTIONS_HASH_KEY_VALUE,
                $name,
                "'" . str_replace("'", '\\\'', $partial) . "'"
            );
        }
        
        return $this->prettyPrint(self::BLOCK_OPTIONS_OPEN)
            . $this->prettyPrint('\r\t')
            . implode($this->prettyPrint(',\r\t'), $partials)
            . $this->prettyPrint(self::BLOCK_OPTIONS_CLOSE);
    }
    
    /**
     * Handlebars will give arguments in a string
     * This will transform them into a legit argument
     * array
     *
     * @param string $string The argument string
     * @return array
     */
    protected function parseArguments(string $string) : array
    {
        $args = array();
        $hash = array();

        $regex = array(
            '([a-zA-Z0-9]+\="[^"]*")',      // cat="meow"
            '([a-zA-Z0-9]+\=\'[^\']*\')',   // mouse='squeak squeak'
            '([a-zA-Z0-9]+\=[a-zA-Z0-9]+)', // dog=false
            '("[^"]*")',                    // "some\'thi ' ng"
            '(\'[^\']*\')',                 // 'some"thi " ng'
            '([^\s]+)'                      // <any group with no spaces>
        );

        preg_match_all('#'.implode('|', $regex).'#is', $string, $matches);

        $stringArgs = $matches[0];
        $name = array_shift($stringArgs);

        $hashRegex = array(
            '([a-zA-Z0-9]+\="[^"]*")',      // cat="meow"
            '([a-zA-Z0-9]+\=\'[^\']*\')',   // mouse='squeak squeak'
            '([a-zA-Z0-9]+\=[a-zA-Z0-9]+)', // dog=false
        );

        foreach ($stringArgs as $arg) {
            //if it's an attribute
            if (!(substr($arg, 0, 1) === "'" && substr($arg, -1) === "'")
                && !(substr($arg, 0, 1) === '"' && substr($arg, -1) === '"')
                && preg_match('#'.implode('|', $hashRegex).'#is', $arg)
            ) {
                list($hashKey, $hashValue) = explode('=', $arg, 2);
                $hash[$hashKey] = $this->parseArgument($hashValue);
                continue;
            }

            $args[] = $this->parseArgument($arg);
        }

        return array($name, $args, $hash);
    }

    /**
     * If there's a quote, null, bool,
     * int, float... it's the literal value
     *
     * @param string $value One string argument value
     * @return mixed
     */
    protected function parseArgument(string $arg)
    {
        //if it's a literal string value
        if (strpos($arg, '"') === 0
            || strpos($arg, "'") === 0
        ) {
            return "'" . str_replace("'", '\\\'', substr($arg, 1, -1)) . "'";
        }

        //if it's null
        if (strtolower($arg) === 'null'
            || strtolower($arg) === 'true'
            || strtolower($arg) === 'false'
            || is_numeric($arg)
        ) {
            return $arg;
        }

        $arg = str_replace(array('[', ']', '(', ')'), '', $arg);
        $arg = str_replace("'", '\\\'', $arg);
        return sprintf(self::BLOCK_ARGUMENT_VALUE, $arg);
    }

    /**
     * Calls an alternative helper to add on to the compiled code
     *
     * @param array $node
     * @return string|false
     */
    protected function tokenize(array $node)
    {
        //lookout for pre processors helper
        $value = explode(' ', $node['value']);

        //is it a helper ?
        $helper = Runtime::getHelper('tokenize-' . $value[0]);

        if (!$helper) {
            return false;
        }

        list($name, $args, $hash) = $this->parseArguments($node['value']);

        //options
        $args[] = array(
            'node'       => $node,
            'name'       => $name,
            'args'       => $node['value'],
            'hash'       => $hash,
            'offset'     => $this->offset,
            'handlebars' => $this->handlebars
        );

        //NOTE: Tokenized do not have data binded to it
        return call_user_func_array($helper, $args);
    }

    /**
     * Makes code look nicely spaced
     *
     * @param string $code
     * @param int $before Used to set the token before spacing
     * @param int $after Used to set the token after spacing
     * @return string
     */
    protected function prettyPrint(string $code, int $before = 0, int $after = 0) : string
    {
        $this->offset += $before;

        if ($this->offset < 0) {
            $this->offset = 0;
        }

        $code = str_replace(
            array('\r', '\n', '\t', '\1', '\2'),
            array("\n", '"\n"',
                str_repeat('    ', $this->offset),
                str_repeat('    ', 1),
                str_repeat('    ', 2)
            ),
            $code
        );

        $this->offset += $after;

        if ($this->offset < 0) {
            $this->offset = 0;
        }

        $code = str_replace('\\{', '{', $code);
        $code = str_replace('\\}', '}', $code);

        //''."\n"
        $code = str_replace(' \'\'."\n"', ' "\n"', $code);

        if ($code === '$buffer .= \'\';') {
            return '';
        }

        return $code;
    }

    /**
     * Finds a particular node in the open sections
     *
     * @param array $open The open nodes
     * @param string $name The last name of the node we are looking for
     * @return int|false The index where the section is found
     */
    protected function findSection(array $open, string $name = self::LAST_OPEN)
    {
        foreach ($open as $i => $item) {
            $item = explode(' ', $item['value']);

            if ($item[0] === $name) {
                return $i;
            }
        }

        if ($name == self::LAST_OPEN) {
            return $i;
        }

        return false;
    }
}
