<?php
declare(strict_types=1);
/**
 * This file was formerly part of the Eden PHP Library.
 * (c) 2014-2016 Openovate Labs
 * (c) 2016 Matthew Gamble
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

namespace MattyG\Handlebars\Test;

use MattyG\Handlebars;

class Compiler extends \PHPUnit_Framework_TestCase
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
        $this->compiler = new Handlebars\Compiler($runtime, new Handlebars\TokenizerFactory(), new Handlebars\ArgumentParserFactory());
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
        $this->assertEquals($template, $code);
    }

    public function testSetOffset()
    {
        $instance = $this->compiler->setOffset(3);
        $this->assertInstanceOf(Handlebars\Compiler::class, $instance);
    }

    public function testParseArguments()
    {
        // TODO: Move these tests into a separate class, as argument parsing is now in a dedicated class
        $parseArgsMethod = new \ReflectionMethod(Handlebars\Compiler::class, "parseArguments");
        $parseArgsMethod->setAccessible(true);

        //basic
        list($name, $args, $hash) = $parseArgsMethod->invoke($this->compiler, "foobar 'merchant' query.profile_type");
        $this->assertCount(2, $args);

        //advanced
        list($name, $args, $hash) = $parseArgsMethod->invoke($this->compiler,
            'foobar 4bar4 4.5 \'some"thi " ng\' 4 "some\'thi \' ng" '
            .'dog=false cat="meow" mouse=\'squeak squeak\'');

        $this->assertEquals('foobar', $name);
        $this->assertCount(5, $args);
        $this->assertEquals('$data->find(\'4bar4\')', $args[0]);
        $this->assertEquals(4.5, $args[1]);
        $this->assertEquals('\'some"thi " ng\'', $args[2]);
        $this->assertEquals(4, $args[3]);
        $this->assertEquals('\'some\\\'thi \\\' ng\'', $args[4]);
        $this->assertCount(3, $hash);
        $this->assertEquals('false', $hash['dog']);
        $this->assertEquals("'meow'", $hash['cat']);
        $this->assertEquals("'squeak squeak'", $hash['mouse']);

        list($name, $args, $hash) = $parseArgsMethod->invoke($this->compiler, '_ \'TODAY\\\'S\' BEST DEALS\'');

        $this->assertEquals('_', $name);
        $this->assertCount(3, $args);
        $this->assertEquals("'TODAY\\'S'", $args[0]);
        $this->assertEquals('$data->find(\'BEST\')', $args[1]);
        $this->assertEquals('$data->find(\'DEALS\\\'\')', $args[2]);
        $this->assertCount(0, $hash);

        list($name, $args, $hash) = $parseArgsMethod->invoke($this->compiler, 'abc x ');

        $this->assertEquals('abc', $name);
        $this->assertCount(1, $args);
        $this->assertEquals('$data->find(\'x\')', $args[0]);
        $this->assertCount(0, $hash);

        list($name, $args, $hash) = $parseArgsMethod->invoke($this->compiler, '___ a "b" cd hash=hashed');

        $this->assertEquals('___', $name);
        $this->assertCount(3, $args);
        $this->assertEquals('$data->find(\'a\')', $args[0]);
        $this->assertEquals("'b'", $args[1]);
        $this->assertEquals('$data->find(\'cd\')', $args[2]);
        $this->assertCount(1, $hash);
        $this->assertEquals('$data->find(\'hashed\')', $hash['hash']);

        list($name, $args, $hash) = $parseArgsMethod->invoke($this->compiler, '__ herp=derp rofl="copter" m');
        $this->assertEquals('__', $name);
        $this->assertCount(1, $args);
        $this->assertEquals('$data->find(\'m\')', $args[0]);
        $this->assertCount(2, $hash);
        $this->assertEquals('$data->find(\'derp\')', $hash['herp']);
        $this->assertEquals("'copter'", $hash['rofl']);
    }
}
