<?php
declare(strict_types=1);

namespace MattyG\Handlebars\Argument;

class ArgumentList
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var Argument[]
     */
    private $args = array();

    /**
     * @var Argument[]
     */
    private $hash = array();

    /**
     * @param string $name
     */
    public function __construct(string $name)
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param Argument $arg
     */
    public function addArgument(Argument $arg)
    {
        $this->args[] = $arg;
    }

    /**
     * @return Argument[]
     */
    public function getArguments(): array
    {
        return $this->args;
    }

    /**
     * @param string $key
     * @param Argument $value
     */
    public function addNamedArgument(string $key, Argument $value)
    {
        $this->hash[$key] = $value;
    }

    /**
     * @return Argument[]
     */
    public function getNamedArguments(): array
    {
        return $this->hash;
    }
}
