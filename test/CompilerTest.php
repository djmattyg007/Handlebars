<?php
/**
 * This file was formerly part of the Eden PHP Library.
 * (c) 2014-2016 Openovate Labs
 * (c) 2016 Matthew Gamble
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

declare(strict_types=1);

namespace MattyG\Handlebars\Test;

use MattyG\Handlebars;
use PHPUnit\Framework\TestCase;

class CompilerTest extends TestCase
{
    /**
     * @var Handlebars\Compiler
     */
    protected $compiler;

    protected function setUp()
    {
        $runtime = new Handlebars\Runtime();
        $runtime->addHelper("startswithfoo", function($value) {
            return "foo{$value}";
        });
        $runtime->addHelper("testinglink", function($url, $text) {
            $string = sprintf('<a href="%1$s">%2$s</a>', $url, $text);
            return new Handlebars\SafeString($string);
        });
        $argumentParserFactory = new Handlebars\Argument\ArgumentParserFactory(new Handlebars\Argument\ArgumentListFactory());
        $this->compiler = new Handlebars\Compiler($runtime, new Handlebars\TokenizerFactory(), $argumentParserFactory);
    }

    protected function tearDown()
    {
        $this->compiler = null;
    }

    public function testCompile()
    {
        $source = trim(file_get_contents(__DIR__ . '/assets/tokenizer.html'));
        $template = file_get_contents(__DIR__ . '/assets/template.php');

        $code = $this->compiler->compile($source);
        $this->assertSame($template, $code);
    }

    /**
     * @expectedException MattyG\Handlebars\Exception
     * @expectedExceptionMessage Missing closing tags for:
     */
    public function testMissingClosingTag()
    {
        $this->compiler->compile('{{#testing}}');
    }

    /**
     * @expectedException MattyG\Handlebars\Exception
     * @expectedExceptionMessage Unknown close tag:
     */
    public function testUnknownClosingTag()
    {
        $this->compiler->compile('{{#testing}}test{{/testing2}}');
    }
}
