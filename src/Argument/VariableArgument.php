<?php
declare(strict_types=1);

namespace MattyG\Handlebars\Argument;

use MattyG\Handlebars\Compiler;

class VariableArgument extends Argument
{
    // Private
    const BLOCK_VARIABLE_ARGUMENT = Compiler::BLOCK_ARGUMENT_VALUE;

    /**
     * @return string
     */
    public function getValue(): string
    {
        $returnValue = str_replace("'", '\\\'', $this->rawValue);
        return sprintf(self::BLOCK_VARIABLE_ARGUMENT, $returnValue);
    }
}
