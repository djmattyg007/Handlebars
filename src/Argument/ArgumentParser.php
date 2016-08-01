<?php
declare(strict_types=1);

namespace MattyG\Handlebars\Argument;

class ArgumentParser
{
    // Private
    const ERROR_NON_WHITESPACE_AFTER_STRING = 'Non-whitespace character detected after string argument: %s';

    /**
     * @var string
     */
    protected $string;

    /**
     * @var int
     */
    protected $strlen;

    /**
     * @var int
     */
    protected $index = 0;

    /**
     * @param string $string The string to be tokenised.
     */
    public function __construct(string $string)
    {
        $this->string = trim($string);
        $this->strlen = mb_strlen($string);
    }

    /**
     * @return array
     * @throws Exception
     */
    public function tokenise() : array
    {
        $name = "";
        $args = array();
        $hash = array();

        $name .= $this->curChar();
        while ($this->nextChar() === true) {
            if ($this->isWhitespace() === true) {
                break;
            }
            $name .= $this->curChar();
        }

        if ($this->canNext() === false) {
            goto finishTokenise;
        }

        while ($this->nextChar() === true) {
            if ($this->isWhitespace() === true) {
                continue;
            }

            $param = $this->gatherArgument();
            if (is_array($param) === true) {
                $hash[$param[0]] = $param[1];
            } else {
                $args[] = $param;
            }
        }

finishTokenise:
        return array($name, $args, $hash);
    }

    /**
     * @return array|string
     * @throws Exception
     */
    protected function gatherArgument()
    {
        $firstChar = $this->curChar();
        $quoteStart = $firstChar === "'" || $firstChar === '"';
        $value = "";

        if ($quoteStart === true) {
            $breakSymbol = $firstChar;
        } else {
            $breakSymbol = null;
            $value .= $firstChar;
        }

        while ($this->nextChar() === true) {
            if ($breakSymbol === null && $this->isWhitespace() === true) {
                break;
            } elseif ($breakSymbol !== null && $this->curChar() === $breakSymbol) {
                if ($this->canNext() === true) {
                    $this->nextChar();
                    if ($this->isWhitespace() === false) {
                        throw new Exception(sprintf(self::ERROR_NON_WHITESPACE_AFTER_STRING, $value));
                    }
                }
                break;
            } elseif ($breakSymbol !== null && $this->curChar() === "\\") {
                $this->nextChar();
                $tempChar = $this->curChar();
                switch ($tempChar) {
                    case "n":
                        $value .= "\n";
                        break;
                    default:
                        $value .= $tempChar;
                        break;
                }
            } elseif ($breakSymbol === null && $this->curChar() === "=") {
                $key = $value;
                $this->nextChar();
                $returnValue = $this->gatherArgument();
                return array($key, $returnValue);
            } else {
                $value .= $this->curChar();
            }
        }

        if ($breakSymbol !== null) {
            return "'" . str_replace("'", '\\\'', $value) . "'";
        } elseif (
            mb_strtolower($value) === "null" ||
            mb_strtolower($value) === "true" ||
            mb_strtolower($value) === "false" ||
            is_numeric($value)
        ) {
            return $value;
        } else {
            $returnValue = str_replace(array("[", "]", "(", ")"), "", $value);
            $returnValue = str_replace("'", '\\\'', $value);
            return sprintf(\MattyG\Handlebars\Compiler::BLOCK_ARGUMENT_VALUE, $returnValue);
        }
    }

    /**
     * @return string
     */
    protected function curChar() : string
    {
        return mb_substr($this->string, $this->index, 1);
    }

    /**
     * Move current index to next character, unless this would create an
     * index out of bounds error.
     *
     * @return bool
     */
    protected function nextChar() : bool
    {
        if ($this->canNext() === true) {
            $this->index++;
            return true;
        } else {
            return false;
        }
    }

    /**
     * @return bool
     */
    protected function canNext() : bool
    {
        return $this->index + 1 < $this->strlen;
    }

    /**
     * Returns true if the current character is whitespace.
     *
     * @return bool
     */
    protected function isWhiteSpace() : bool
    {
        $curChar = $this->curChar();
        return trim($curChar) !== $curChar;
    }
}
