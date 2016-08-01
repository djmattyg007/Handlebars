<?php
declare(strict_types=1);
/**
 * This file was formerly part of the Eden PHP Library.
 * (c) 2014-2016 Openovate Labs
 * (c) 2016 Matthew Gamble
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

namespace MattyG\Handlebars;

class Compiler
{
    const BLOCK_TEXT_LINE = '\r\t$buffer .= \'%s\'.\n;';
    const BLOCK_TEXT_LAST = '\r\t$buffer .= \'%s\';';

    const BLOCK_VARIABLE_VALUE = '\r\t$buffer .= htmlspecialchars($data->find(\'%s\'), ENT_COMPAT, \'UTF-8\');\r';
    const BLOCK_ESCAPE_VALUE = '\r\t$buffer .= $data->find(\'%s\');\r';

    const BLOCK_HELPER_OPEN = '\r\t$helperResult = $this->runtime->getHelper(\'%s\')(';
    const BLOCK_HELPER_CLOSE = '\r\t);';
    const BLOCK_HELPER_VARIABLE_RESULTCHECK = '\r\t$buffer .= $helperResult instanceof SafeString ? $helperResult : htmlspecialchars($helperResult, ENT_COMPAT, \'UTF-8\');\r';
    const BLOCK_HELPER_ESCAPE_RESULTCHECK = '\r\t$buffer .= $helperResult;\r';

    const BLOCK_ESCAPE_HELPER_OPEN = '\r\t$buffer .= $this->runtime->getHelper(\'%s\')(';
    const BLOCK_ESCAPE_HELPER_CLOSE = '\r\t);\r';

    const BLOCK_ARGUMENT_VALUE = '$data->find(\'%s\')';

    const BLOCK_OPTIONS_OPEN = 'array(';
    const BLOCK_OPTIONS_CLOSE = '\r\t)';

    const BLOCK_OPTIONS_FN_OPEN = '\r\t\'fn\' => function($context = null) use ($data) {';
    const BLOCK_OPTIONS_FN_BODY_1 = '\r\t\1if (is_array($context)) {';
    const BLOCK_OPTIONS_FN_BODY_2 = '\r\t\1\1$data->push($context);';
    const BLOCK_OPTIONS_FN_BODY_3 = '\r\t\1}';
    const BLOCK_OPTIONS_FN_BODY_4 = '\r\r\t\1$buffer = \'\';';
    const BLOCK_OPTIONS_FN_BODY_5 = '\r\r\t\1if (is_array($context)) {';
    const BLOCK_OPTIONS_FN_BODY_6 = '\r\t\1\1$data->pop();';
    const BLOCK_OPTIONS_FN_BODY_7 = '\r\t\1}';
    const BLOCK_OPTIONS_FN_CLOSE = '\r\r\t\1return $buffer;\r\t},\r';

    const BLOCK_OPTIONS_INVERSE_OPEN = '\r\t\'inverse\' => function($context = null) use ($data) {';
    const BLOCK_OPTIONS_INVERSE_BODY_1 = '\r\t\1if (is_array($context)) {';
    const BLOCK_OPTIONS_INVERSE_BODY_2 = '\r\t\1\1$data->push($context);';
    const BLOCK_OPTIONS_INVERSE_BODY_3 = '\r\t\1}';
    const BLOCK_OPTIONS_INVERSE_BODY_4 = '\r\r\t\1$buffer = \'\';';
    const BLOCK_OPTIONS_INVERSE_BODY_5 = '\r\r\t\1if (is_array($context)) {';
    const BLOCK_OPTIONS_INVERSE_BODY_6 = '\r\t\1\1$data->pop();';
    const BLOCK_OPTIONS_INVERSE_BODY_7 = '\r\t\1}';
    const BLOCK_OPTIONS_INVERSE_CLOSE = '\r\r\t\1return $buffer;\r\t}\r';

    const BLOCK_OPTIONS_FN_EMPTY = '\r\t\'fn\' => function() {},';
    const BLOCK_OPTIONS_INVERSE_EMPTY = '\r\t\'inverse\' => function() {},';
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
     * @var Runtime
     */
    protected $runtime;

    /**
     * @var TokenizerFactory
     */
    protected $tokenizerFactory;

    /**
     * @var ArgumentParserFactory
     */
    protected $argumentParserFactory;

    /**
     * @var int
     */
    protected $offset = 1;

    /**
     * Just load the source template
     *
     * @param Runtime $runtime
     * @param TokenizerFactory $tokenizerFactory
     * @param ArgumentParserFactory $argumentParserFactory
     */
    public function __construct(Runtime $runtime, TokenizerFactory $tokenizerFactory, ArgumentParserFactory $argumentParserFactory)
    {
        $this->runtime = $runtime;
        $this->tokenizerFactory = $tokenizerFactory;
        $this->argumentParserFactory = $argumentParserFactory;
    }

    /**
     * Transform the template to code that can be used independently
     *
     * @param string $source
     * @param int $startingOffset
     * @return string
     */
    public function compile(string $source, int $startingOffset = 1) : string
    {
        $this->offset = $startingOffset;
        $tokenizer = $this->tokenizerFactory->create($source);
        $buffer = '';
        $open = array();

        $tokenizer->tokenize(function ($node) use (&$buffer, &$open) {
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

        // Indent all of the code by one level because it's destined for a class method.
        $indentedCode = str_replace("\n", "\n    ", $buffer);
        // Strip trailing whitespace from otherwise blank lines.
        return preg_replace('/^    $/m', '', $indentedCode);
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

        //lookout for partials
        if (substr($node["value"], 0, 2) === "> ") {
            return $this->generatePartial($node);
        }

        list($name, $args, $hash) = $this->parseArguments($node['value']);

        //if it's a helper
        if ($this->runtime->getHelper($name)) {
            return $this->generateHelper($name, $node['value'], $args, $hash, self::BLOCK_HELPER_VARIABLE_RESULTCHECK);
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

        //lookout for partials
        if (substr($node["value"], 2) === "> ") {
            return $this->generatePartial($node);
        }

        list($name, $args, $hash) = $this->parseArguments($node['value']);

        //if it's a helper
        if ($this->runtime->getHelper($name)) {
            return $this->generateHelper($name, $node['value'], $args, $hash, self::BLOCK_HELPER_ESCAPE_RESULTCHECK);
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
        if (is_null($this->runtime->getHelper($name))) {
            //run each
            $node['value'] = 'each ' . $node['value'];
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
            . $this->prettyPrint('\r\t' . implode(',\r\t', $args), 1, 2);
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
     * @param array $node
     * @return string
     */
    protected function generatePartial(array $node) : string
    {
        list($name, $args, $optionsHash) = $this->parseArguments($node['value']);

        //get the name
        //it will be like 'something'
        //or $data->find('something')
        $name = array_shift($args);

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
        $partial = $this->runtime->getPartial($name);

        //but if the partial is null
        if (is_null($partial) === true) {
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
        foreach ($optionsHash as $key => $value) {
            $hash[$key] = sprintf('\'%s\' => %s', $key, $value);
        }

        if (empty($hash)) {
            $hash = '';
        } else {
            $hash = '\r\t\1\1\1' . implode(', \r\t\1\1\1', $hash) . '\r\t\1\1';
        }

        $layout = str_replace(
            array('\r', '\t', '\1'),
            array("\n",
                str_repeat('    ', $this->offset),
                str_repeat('    ', 1)
            ),
            '\r\t$buffer .= $this->runtime->getHelper(\'noop\')('
            . $scope
            . '\r\t\1array('
            . '\r\t\1\1\'name\' => \'noop\','
            . '\r\t\1\1\'hash\' => array(' . $hash . '),'
            . '\r\t\1\1\'fn\' => function($context = null) use ($data) {'
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
            . '\r\t\1\1\'inverse\' => function() {},'
            . '\r\t\1)'
            . '\r\t);\r'
        );

        $compiler = new static($this->runtime, $this->tokenizerFactory, $this->argumentParserFactory);
        $code = $compiler->compile($partial, $this->offset + 3);

        return sprintf($layout, $code);
    }

    /**
     * @param string $name
     * @param string $nodeValue
     * @param array $args
     * @param array $hash
     * @param string $closingTag
     */
    protected function generateHelper(string $name, string $nodeValue, array $args, array $hash, string $closingTag)
    {
        //form hash
        foreach ($hash as $key => $value) {
            $hash[$key] = sprintf(self::BLOCK_OPTIONS_HASH_KEY_VALUE, $key, $value);
        }

        $args[] = $this->prettyPrint(self::BLOCK_OPTIONS_OPEN, 0, 2)
            . $this->prettyPrint(sprintf(self::BLOCK_OPTIONS_NAME, $name))
            . $this->prettyPrint(sprintf(self::BLOCK_OPTIONS_ARGS, str_replace("'", '\\\'', $nodeValue)))
            . $this->prettyPrint(sprintf(self::BLOCK_OPTIONS_HASH, implode(', \r\t', $hash)))
            . $this->prettyPrint(self::BLOCK_OPTIONS_FN_EMPTY)
            . $this->prettyPrint(self::BLOCK_OPTIONS_INVERSE_EMPTY)
            . $this->prettyPrint(self::BLOCK_OPTIONS_CLOSE, -1);

        return $this->prettyPrint(sprintf(self::BLOCK_HELPER_OPEN, $name), -1)
            . $this->prettyPrint('\r\t' . implode(',\r\t', $args), 1, -1)
            . $this->prettyPrint(self::BLOCK_HELPER_CLOSE)
            . $this->prettyPrint($closingTag);
    }

    /**
     * Handlebars will give arguments in a string. This will transform them
     * into a legitimate argument array.
     *
     * @param string $string The argument string.
     * @return array
     * @throws Exception
     */
    protected function parseArguments(string $string) : array
    {
        $argParser = $this->argumentParserFactory->create($string);
        return $argParser->tokenise();
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
