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
     * @var ArgumentListFactory
     */
    protected $argumentListFactory;

    /**
     * @var int
     */
    protected $index = 0;

    /**
     * @param string $string The string to be tokenised.
     */
    public function __construct(string $string, ArgumentListFactory $argumentListFactory)
    {
        $this->string = trim($string);
        $this->strlen = mb_strlen($string);
        $this->argumentListFactory = $argumentListFactory;
    }

    /**
     * @return ArgumentList
     * @throws Exception
     */
    public function tokenise() : ArgumentList
    {
        $this->index = 0;

        $name = "";
        $name .= $this->curChar();
        while ($this->nextChar() === true) {
            if ($this->isWhitespace() === true) {
                break;
            }
            $name .= $this->curChar();
        }
        $argumentList = $this->argumentListFactory->create($name);

        if ($this->canNext() === false) {
            goto finishTokenise;
        }

        while ($this->nextChar() === true) {
            if ($this->isWhitespace() === true) {
                continue;
            }

            $param = $this->gatherArgument();
            if (is_array($param) === true) {
                $argumentList->addNamedArgument($param[0], $param[1]);
            } else {
                $argumentList->addArgument($param);
            }
        }

finishTokenise:
        return $argumentList;
    }

    /**
     * @return Argument|array
     * @throws Exception
     */
    protected function gatherArgument()
    {
        $firstChar = $this->curChar();
        if ($firstChar === "(") {
            return $this->gatherHelperArgument();
        }
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
            return new StringArgument($value);
        } elseif (
            mb_strtolower($value) === "null" ||
            mb_strtolower($value) === "true" ||
            mb_strtolower($value) === "false" ||
            is_numeric($value)
        ) {
            return new Argument($value);
        } else {
            return new VariableArgument($value);
        }
    }

    /**
     * @return HelperArgument
     */
    protected function gatherHelperArgument() : HelperArgument
    {
        $startIndex = $this->index;

        // Cycle through the name of the helper
        $startOfNameReached = false;
        $endOfNameReached = false;
        while ($endOfNameReached === false && $this->nextChar() === true) {
            $isWhitespace = $this->isWhitespace();
            if ($startOfNameReached === true && $isWhitespace === true) {
                $endOfNameReached = true;
            } elseif ($this->curChar() === ")") {
                // When there are no arguments to the helper
                goto finishHelperScan;
            } elseif ($startOfNameReached === false && $isWhitespace === true) {
                continue;
            } elseif ($startOfNameReached === false && $isWhitespace === false) {
                $startOfNameReached = true;
            }
        }

        $this->nextChar();
        $curChar = $this->curChar();
        if ($curChar === ")") {
            goto finishHelperScan;
        } elseif ($curChar === "'" || $curChar === '"') {
            $currentQuote = $curChar;
        } else {
            $currentQuote = null;
        }

        // TODO: Add depth counter for () pairs to support doubly-nested helpers
        while ($this->nextChar() === true) {
            $curChar = $this->curChar();
            $isWhitespace = $this->isWhitespace();
            if ($currentQuote === null && $curChar === ")") {
                break;
            } elseif ($currentQuote === null && $isWhitespace === true) {
                $this->nextChar();
                $curChar = $this->curChar();
                if ($curChar === ")") {
                    goto finishHelperScan;
                } elseif ($curChar === "'" || $curChar === '"') {
                    $currentQuote = $curChar;
                }
            } elseif ($currentQuote !== null && $curChar === "\\") {
                $this->nextChar();
            } elseif ($currentQuote !== null && $curChar === $currentQuote) {
                $currentQuote = null;
            }
        }

finishHelperScan:
        $endIndex = $this->index;
        $helperArgString = mb_substr($this->string, $startIndex + 1, $endIndex - $startIndex - 1);
        $helperArgParser = new static($helperArgString, $this->argumentListFactory);
        return new HelperArgument($helperArgString, $helperArgParser->tokenise());
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
