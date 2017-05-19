<?php
/**
 * This file was formerly part of the Eden PHP Library.
 * (c) 2014-2016 Openovate Labs
 * (c) 2016 Matthew Gamble
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

declare(strict_types=1);

namespace MattyG\Handlebars;

class Tokenizer
{
    const TYPE_TEXT = 'text';

    const TYPE_VARIABLE_ESCAPE = 'escape';
    const TYPE_VARIABLE_UNESCAPE = 'variable';

    const TYPE_SECTION_OPEN = 'section';
    const TYPE_SECTION_CLOSE = 'close';

    /**
     * @var string
     */
    protected $source;

    /**
     * @var string
     */
    protected $buffer = '';

    /**
     * @var string
     */
    protected $type = self::TYPE_TEXT;

    /**
     * @var string
     */
    protected $level = 0;

    /**
     * Just load the source template
     *
     * @param string $source
     */
    public function __construct(string $source)
    {
        $this->source = $this->trim($source);
        $this->strlen = mb_strlen($this->source);
        $this->reset();
    }

    private function reset()
    {
        $this->buffer = '';
        $this->type = self::TYPE_TEXT;
        $this->level = 0;
    }

    /**
     * Main rendering function that passes tokens to the
     * supplied callback.
     *
     * @param callable|null $callback
     * @return Tokenizer
     */
    public function tokenize($callback = null)
    {
        $this->reset();
        if (!is_callable($callback)) {
            $callback = function() {};
        }

        for ($line = 1, $i = 0; $i < $this->strlen; $i++) {
            if (mb_substr($this->source, $i, 1) === "\n") {
                $line++;
            }

            switch (true) {
                //section
                case mb_substr($this->source, $i, 3) == '{{{#':
                    $i = $this->addNode($i, self::TYPE_SECTION_OPEN, $line, 4, 6, $callback);
                    break;
                case mb_substr($this->source, $i, 3) == '{{#':
                    $i = $this->addNode($i, self::TYPE_SECTION_OPEN, $line, 3, 5, $callback);
                    break;
                case mb_substr($this->source, $i, 3) == '{{{/':
                    $i = $this->addNode($i, self::TYPE_SECTION_CLOSE, $line, 4, 6, $callback);
                    break;
                case mb_substr($this->source, $i, 3) == '{{/':
                    $i = $this->addNode($i, self::TYPE_SECTION_CLOSE, $line, 3, 5, $callback);
                    break;

                //variable
                case mb_substr($this->source, $i, 3) == '{{{':
                    $i = $this->addNode($i, self::TYPE_VARIABLE_ESCAPE, $line, 3, 6, $callback);
                    break;
                case mb_substr($this->source, $i, 2) == '{{':
                    $i = $this->addNode($i, self::TYPE_VARIABLE_UNESCAPE, $line, 2, 4, $callback);
                    break;

                //text
                default:
                    $this->buffer .= mb_substr($this->source, $i, 1);
                    break;
            }
        }

        $this->flushText($i, $callback);
        return $this;
    }

    /**
     * Forms the node and passes to the callback
     *
     * @param int $start
     * @param string $type
     * @param int $line
     * @param int $offset1
     * @param int $offset2
     * @param callable $callback
     * @return Tokenizer
     */
    protected function addNode(int $start, string $type, int $line, int $offset1, int $offset2, $callback)
    {
        $this->flushText($start, $callback);

        switch ($type) {
            case self::TYPE_VARIABLE_ESCAPE:
                $end = $this->findVariable($start, true);
                break;
            case self::TYPE_VARIABLE_UNESCAPE:
                $end = $this->findVariable($start, false);
                break;
            case self::TYPE_SECTION_OPEN:
                $end = $this->findVariable($start, false);
                break;
            case self::TYPE_SECTION_CLOSE:
            default:
                $end = $this->findVariable($start, false);
                $this->level--;
                break;
        }

        call_user_func($callback, array(
            'type'  => $type,
            'line'  => $line,
            'start' => $start,
            'end'   => $end,
            'level' => $this->level,
            'value' => mb_substr($this->source, $start + $offset1, $end - $start - $offset2)
        ), $this->source);

        if ($type === self::TYPE_SECTION_OPEN) {
            $this->level++;
        }

        return $end - 1;
    }

    /**
     * Takes whatever is in the buffer
     * forms a node and passes it to
     * the callback
     *
     * @param int $i
     * @param callable $callback
     * @return Tokenizer
     */
    protected function flushText(int $i, callable $callback): Tokenizer
    {
        if ($this->type !== self::TYPE_TEXT || !mb_strlen($this->buffer)) {
            return $this;
        }

        call_user_func($callback, array(
            'type'  => $this->type,
            'start' => $i - mb_strlen($this->buffer),
            'end'   => $i - 1,
            'level' => $this->level,
            'value' => $this->buffer
        ), $this->source);

        //flush
        $this->buffer = '';

        return $this;
    }

    /**
     * Since we know where the start is,
     * we need to find the end in the source.
     *
     * @param int $i
     * @param bool $escape
     * @return int
     */
    protected function findVariable(int $i, bool $escape): int
    {
        $close = ($escape === true ? '}}}' : '}}');

        for (; mb_substr($this->source, $i, mb_strlen($close)) !== $close; $i++) {
        }

        return $i + mb_strlen($close);
    }

    /**
     * Quick trim script
     *
     * @param string $string
     * @return string
     */
    protected function trim(string $string): string
    {
        $string = preg_replace('#\s*\{\{\{\~\s*#is', '{{{', $string);
        $string = preg_replace('#\s*\~\}\}\}\s*#is', '}}}', $string);
        $string = preg_replace('#\s*\{\{\~\s*#is', '{{', $string);
        $string = preg_replace('#\s*\~\}\}\s*#is', '}}', $string);
        return $string;
    }
}
