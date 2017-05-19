<?php
declare(strict_types=1);

namespace MattyG\Handlebars\Argument;

class Argument
{
    /**
     * @var string
     */
    protected $rawValue;

    /**
     * @param string $value
     */
    public function __construct(string $value)
    {
        $this->rawValue = $value;
    }

    /**
     * @return string
     */
    public function getValue(): string
    {
        return $this->rawValue;
    }

    /**
     * @return string
     */
    public function getRawValue(): string
    {
        return $this->rawValue;
    }
}
