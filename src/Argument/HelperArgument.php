<?php
declare(strict_types=1);

namespace MattyG\Handlebars\Argument;

class HelperArgument extends Argument
{
    /**
     * @var ArgumentList
     */
    protected $argumentList;

    /**
     * @param string $value
     * @param ArgumentList $argumentList
     */
    public function __construct(string $value, ArgumentList $argumentList)
    {
        parent::__construct($value);
        $this->argumentList = $argumentList;
    }

    /**
     * @return string
     */
    public function getValue() : string
    {
        return "(" . $this->rawValue . ")";
    }

    /**
     * @return ArgumentList
     */
    public function getArgumentList() : ArgumentList
    {
        return $this->argumentList;
    }
}
