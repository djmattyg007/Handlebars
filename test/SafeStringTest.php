<?php
declare(strict_types=1);

namespace MattyG\Handlebars\Test;

use MattyG\Handlebars;
use PHPUnit\Framework\TestCase;

class SafeStringTest extends TestCase
{
    /**
     * @var Handlebars\Runtime
     */
    protected $runtime;

    protected function setUp()
    {
        $this->runtime = new Handlebars\Runtime();
        $this->runtime->addHelper("testinglink", function($url, $text) {
            $string = sprintf('<a href="%1$s">%2$s</a>', $url, $text);
            return new Handlebars\SafeString($string);
        });
    }

    protected function tearDown()
    {
        $this->runtime = null;
    }

    public function testManualSafeString()
    {
        $content = "this is a test including some <html></html>";
        $safeString = new Handlebars\SafeString($content);
        $this->assertSame($content, (string) $safeString);
    }

    public function testReturnedSafeString()
    {
        $testinglinkHelper = $this->runtime->getHelper("testinglink");
        $result = $testinglinkHelper("https://github.com", "GitHub");
        $this->assertInstanceOf(Handlebars\SafeString::class, $result);
        $this->assertSame('<a href="https://github.com">GitHub</a>', (string) $result);
    }
}
