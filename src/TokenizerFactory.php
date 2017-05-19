<?php
declare(strict_types=1);

namespace MattyG\Handlebars;

class TokenizerFactory
{
    /**
     * @var string
     */
    private $classname;

    /** 
     * @param string $classname
     */
    public function __construct(string $classname = Tokenizer::class)
    {   
        $this->classname = $classname;
    }   

    /**
     * @param string $source
     * @return Tokenizer
     */
    public function create(string $source): Tokenizer
    {   
        $classname = $this->classname;
        return new $classname($source);
    }   
}
