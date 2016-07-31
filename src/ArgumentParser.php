<?php
declare(strict_types=1);

namespace MattyG\Handlebars;

class ArgumentParser
{
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
                $this->nextChar();
            } else {
                $param = $this->gatherArgument();
                if (is_array($param) === true) {
                    $hash[$param[0]] = $param[1];
                } else {
                    $args[] = $param;
                }
            }
        }

finishTokenise:
        return array($name, $args, $hash);
    }

    /**
     * @return array|string
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
                $this->nextChar();
                // TODO: Throw exception if next character is not whitespace
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
            return sprintf(Compiler::BLOCK_ARGUMENT_VALUE, $returnValue);
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
