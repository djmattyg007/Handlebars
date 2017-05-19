<?php
declare(strict_types=1);

namespace MattyG\Handlebars\Argument;

class StringArgument extends Argument
{
    /**
     * @return string
     */
    public function getValue(): string
    {
        return "'" . str_replace("'", '\\\'', $this->rawValue) . "'";
    }
}
