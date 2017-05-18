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

class HandlebarsTest extends TestCase
{
    /**
     * @var HandlebarsMain
     */
    protected $handlebars;

    protected function setUp()
    {
        $runtime = new Handlebars\Runtime(true);
        $argumentParserFactory = new Handlebars\Argument\ArgumentParserFactory(new Handlebars\Argument\ArgumentListFactory());
        $compiler = new Handlebars\Compiler($runtime, new Handlebars\TokenizerFactory(), $argumentParserFactory);
        $this->handlebars = new Handlebars\Handlebars($runtime, $compiler, new Handlebars\DataFactory());
    }

    protected function tearDown()
    {
        $this->handlebars = null;
    }

    public function testNewInstance()
    {
        $handlebars = Handlebars\Handlebars::newInstance();
        $this->assertInstanceOf(Handlebars\Handlebars::class, $handlebars);
    }

    public function testCompile() 
    {
        $template = $this->handlebars->compile('{{foo}}{{{foo}}}');
        $results = $template(array('foo' => '<strong>foo</strong>'));
        $this->assertEquals('&lt;strong&gt;foo&lt;/strong&gt;<strong>foo</strong>', $results); 
    }

    public function testSetCachePath()
    {
        $this->assertInstanceOf(Handlebars\Handlebars::class, $this->handlebars->setCachePath("/foo/bar"));
    }

    public function testRegisterHelper1() 
    {
        //simple helper
        $this->handlebars->registerHelper('root', function() {
            return '/some/root';
        });

        $template = $this->handlebars->compile('{{root}}/bower_components/eve-font-awesome/awesome.css');
        $result = $template();
        $this->assertEquals('/some/root/bower_components/eve-font-awesome/awesome.css', $result);
    }

    public function testRegisterHelper2()
    {
        $this->handlebars->registerHelper('pathjoin', function() {
            $args = func_get_args();
            $options = array_pop($args);
            return implode(DIRECTORY_SEPARATOR, $args);
        });

        $template = $this->handlebars->compile('/{{ pathjoin (concat prefix "/bin") "php" }}');
        $result = $template->render(array("prefix" => "usr" . DIRECTORY_SEPARATOR . "local"));
        $this->assertEquals('/usr/local/bin/php', $result);
    }

    public function testRegisterHelper3()
    {
        $found = false;
        $this->handlebars->registerHelper('foo', function(
            $bar, 
            $four, 
            $true, 
            $null, 
            $false,
            $zoo
        ) use (&$found) {
            $this->assertEquals('', $bar);
            $this->assertEquals(4, $four);
            $this->assertTrue($true);
            $this->assertNull($null);
            $this->assertFalse($false);
            $this->assertEquals('foobar', $zoo);
            $found = true;
            return $four + 1;
        });
        $template = $this->handlebars->compile('{{foo bar 4 true null false zoo}}');

        $result = $template(array('zoo' => 'foobar'));
        $this->assertTrue($found);
        $this->assertEquals(5, $result);
    }

    public function testRegisterHelper4()
    {
        $found = false;
        $this->handlebars->registerHelper('foo', function(
            $number, 
            $something1, 
            $number2, 
            $something2
        ) use (&$found) {
            $this->assertEquals(4.5, $number);
            $this->assertEquals(4, $number2);
            $this->assertEquals('some"thi " ng', $something1);
            $this->assertEquals("some'thi ' ng", $something2);
            $found = true;
            return $something1.$something2;
        });
        $template = $this->handlebars->compile('{{{foo 4.5 \'some"thi " ng\' 4 "some\'thi \' ng"}}}');

        $result = $template();
        $this->assertTrue($found);
        $this->assertEquals('some"thi " ng'."some'thi ' ng", $result);
    }

    public function testRegisterHelper5()
    {
        //attributes test
        $found = false;
        $this->handlebars->registerHelper('foo', function(
                $bar, 
                $number,
                $something1, 
                $number2, 
                $something2,
                $options
            ) use (&$found) {
                $this->assertEquals(4.5, $number);
                $this->assertEquals(4, $number2);
                $this->assertEquals('some"thi " ng', $something1);
                $this->assertEquals("some'thi ' ng", $something2);
                $this->assertFalse($options['hash']['dog']);
                $this->assertEquals('meow', $options['hash']['cat']);
                $this->assertEquals('squeak squeak', $options['hash']['mouse']);
                $found = true;
                return $number2 + 1;
            });
        $template = $this->handlebars->compile(
                '{{foo 4bar4 4.5 \'some"thi " ng\' 4 "some\'thi \' ng" '
                .'dog=false cat="meow" mouse=\'squeak squeak\'}}');

        $results = $template(array('zoo' => 'foobar'));
        $this->assertTrue($found);
        $this->assertEquals(5, $results);
    }

    public function testRegisterPartial1()
    {
        //basic
        $this->handlebars->registerPartial('foo', 'This is {{ foo }}');
        $this->handlebars->registerPartial('bar', 'Foo is not {{ bar }}');

        $template = $this->handlebars->compile('{{> foo }} ... {{> bar }}');
        $result = $template(array('foo' => 'FOO', 'bar' => 'BAR'));
        $this->assertEquals('This is FOO ... Foo is not BAR', $result);
    }

    public function testRegisterPartial2()
    {
        //with scope
        $this->handlebars->registerPartial('foo', 'This is {{ foo }}');
        $this->handlebars->registerPartial('bar', 'Foo is not {{ bar }}');

        $template = $this->handlebars->compile('{{> foo }} ... {{> bar zoo}}');
        $result = $template(array('foo' => 'FOO', 'bar' => 'BAR', 'zoo' => array('bar' => 'ZOO')));
        $this->assertEquals('This is FOO ... Foo is not ZOO', $result);
    }

    public function testRegisterPartial3()
    {
        //with attributes
        $this->handlebars->registerPartial('foo', 'This is {{ foo }}');
        $this->handlebars->registerPartial('bar', 'Foo is not {{ something }}');

        $template = $this->handlebars->compile('{{> foo }} ... {{> bar zoo something="Amazing"}}');
        $result = $template(array('foo' => 'FOO', 'bar' => 'BAR', 'zoo' => array('bar' => 'ZOO')));
        $this->assertEquals('This is FOO ... Foo is not Amazing', $result);
    }

    public function testSetNamePrefix()
    {
        $instance = $this->handlebars->setNamePrefix('foobar');
        $this->assertInstanceOf(Handlebars\Handlebars::class, $instance);
    }
}
