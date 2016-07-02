<?php
declare(strict_types=1);

namespace MattyG\Handlebars;

class SafeString
{
    /**
     * @var string
     */
    private $safeString;

    /**
     * @param string $safeString
     */
    public function __construct(string $safeString)
    {
        $this->safeString = $safeString;
    }

    /**
     * @return string
     */
    public function __toString() : string
    {
        return $this->safeString;
    }
}
