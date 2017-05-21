<?php
declare(strict_types=1);

namespace MattyG\Handlebars\Test\_Helpers;

use Psr\Log\AbstractLogger;
use Psr\Log\LoggerInterface;

class ArrayLogger extends AbstractLogger implements LoggerInterface
{
    protected $

    /**
     * Logs with an arbitrary level.
     *
     * @param mixed $level
     * @param string $message
     * @param array $context
     */
    public function log($level, $message, array $context = array())
    {
    }
}
