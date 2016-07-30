<?php
declare(strict_types=1);

namespace MattyG\Handlebars;

class ArgumentParserFactory
{
    /**
     * @var string
     */
    private $classname;

    /** 
     * @param string $classname
     */
    public function __construct(string $classname = ArgumentParser::class)
    {   
        $this->classname = $classname;
    }   

    /**
     * @param string $string
     * @return ArgumentParser
     */
    public function create(string $string) : ArgumentParser
    {   
        $classname = $this->classname;
        return new $classname($string);
    }   
}
