<?php
declare(strict_types=1);

namespace MattyG\Handlebars\Argument;

class ArgumentParserFactory
{
    /**
     * @var string
     */
    private $classname;

    /**
     * @var ArgumentListFactory
     */
    private $argumentListFactory;

    /**
     * @param ArgumentListFactory $argumentListFactory
     * @param string $classname
     */
    public function __construct(ArgumentListFactory $argumentListFactory, string $classname = ArgumentParser::class)
    {
        $this->classname = $classname;
        $this->argumentListFactory = $argumentListFactory;
    }

    /**
     * @param string $string
     * @return ArgumentParser
     */
    public function create(string $string) : ArgumentParser
    {
        $classname = $this->classname;
        return new $classname($string, $this->argumentListFactory);
    }
}
