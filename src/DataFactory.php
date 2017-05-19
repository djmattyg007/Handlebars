<?php
declare(strict_types=1);

namespace MattyG\Handlebars;

class DataFactory
{
    /**
     * @var string
     */
    private $classname;

    /** 
     * @param string $classname
     */
    public function __construct(string $classname = Data::class)
    {   
        $this->classname = $classname;
    }   

    /**
     * @param array $data
     * @return Data
     */
    public function create(array $data): Data
    {   
        $classname = $this->classname;
        return new $classname($data);
    }   
}
