<?php
declare(strict_types=1);

namespace MattyG\Handlebars\Argument;

class ArgumentListFactory
{
    /**
     * @var string
     */
    private $classname;

    /**
     * @param string $classname
     */
    public function __construct(string $classname = ArgumentList::class)
    {
        $this->classname = $classname;
    }

    /**
     * @param string $name
     * @return ArgumentList
     */
    public function create(string $name): ArgumentList
    {
        $classname = $this->classname;
        return new $classname($name);
    }
}
