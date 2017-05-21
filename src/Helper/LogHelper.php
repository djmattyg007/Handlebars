<?php
declare(strict_types=1);

namespace MattyG\Handlebars\Helper;

use Psr\Log\InvalidArgumentException;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;

class LogHelper
{
    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @param LoggerInterface $logger
     */
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * We accept "warn" as a special case for "warning" because the
     * Handlebars.js log helper uses "warn". Unlike the Handlebars.js
     * log helper, we allow all log levels allowed by PSR-3.
     *
     * @param string $text
     * @param $options
     */
    public function __invoke($text, $options)
    {
        $hash = $options["hash"];
        if (empty($hash["level"])) {
            $level = LogLevel::INFO;
        } elseif ($hash["level"] === "warn") {
            $level = LogLevel::WARNING;
        } else {
            $level = @constant(LogLevel::class . "::" . strtoupper($hash["level"]));
            if ($level === null) {
                throw new InvalidArgumentException(sprintf("Unknown log level '%s' specified.", $hash["level"]));
            }
        }
        unset($hash["level"]);
        $this->logger->log($level, (string) $text, $hash);
    }
}
