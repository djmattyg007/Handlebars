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
 
class RuntimeTest extends TestCase
{
    /**
     * @var callable
     */
    protected $compilerFactory;

    /**
     * @var Handlebars\DataFactory
     */
    protected $dataFactory;

    protected function setUp()
    {
        $this->compilerFactory = function(Handlebars\Runtime $runtime) {
            $argumentParserFactory = new Handlebars\Argument\ArgumentParserFactory(new Handlebars\Argument\ArgumentListFactory());
            return new Handlebars\Compiler($runtime, new Handlebars\TokenizerFactory(), $argumentParserFactory);
        };
        $this->dataFactory = new Handlebars\DataFactory();
    }

    public function testGetHelper()
    {
        $runtime1 = new Handlebars\Runtime();
        $runtime2 = new Handlebars\Runtime(false);

        $this->assertTrue(is_callable($runtime1->getHelper("if")));
        $this->assertInstanceOf(Handlebars\Helper\EachHelper::class, $runtime1->getHelper("each"));
        $this->assertNull($runtime1->getHelper("bar"));

        $this->assertNull($runtime2->getHelper("if"));
    }

    public function testAddHelper1()
    {
        $runtime = new Handlebars\Runtime(false);
        $runtime->addHelper("root", function() {
            return "/some/root";
        });
        $handlebars = new Handlebars\Handlebars($runtime, call_user_func($this->compilerFactory, $runtime), $this->dataFactory);

        $template = $handlebars->compile('{{root}}/bower_components/eve-font-awesome/awesome.css');

        $result = $template();
        $this->assertSame('/some/root/bower_components/eve-font-awesome/awesome.css', $result);
    }

    public function testAddHelper2()
    {
        $found = false;
        $runtime = new Handlebars\Runtime(false);
        $runtime->addHelper("foo", function(
            $bar, 
            $four, 
            $true, 
            $null, 
            $false,
            $zoo
        ) use (&$found) {
            $this->assertSame(null, $bar);
            $this->assertSame(4, $four);
            $this->assertTrue($true);
            $this->assertNull($null);
            $this->assertFalse($false);
            $this->assertSame('foobar', $zoo);
            $found = true;
            return $four + 1;
        });
        $handlebars = new Handlebars\Handlebars($runtime, call_user_func($this->compilerFactory, $runtime), $this->dataFactory);

        $template = $handlebars->compile('{{foo bar 4 true null false zoo}}');

        $result = $template(array("zoo" => "foobar"));
        $this->assertTrue($found);
        $this->assertSame("5", $result);
    }

    public function testAddHelper3()
    {
        $found = false;
        $runtime = new Handlebars\Runtime(false);
        $runtime->addHelper("foo", function(
            $number, 
            $something1, 
            $number2, 
            $something2
        ) use (&$found) {
            $this->assertSame(4.5, $number);
            $this->assertSame(4, $number2);
            $this->assertSame('some"thi " ng', $something1);
            $this->assertSame("some'thi ' ng", $something2);
            $found = true;
            return $something1 . $something2;
        });
        $handlebars = new Handlebars\Handlebars($runtime, call_user_func($this->compilerFactory, $runtime), $this->dataFactory);

        $template = $handlebars->compile('{{{foo 4.5 \'some"thi " ng\' 4 "some\'thi \' ng"}}}');

        $result = $template();
        $this->assertTrue($found);
        $this->assertSame('some"thi " ng'."some'thi ' ng", $result);
    }

    public function testAddHelper4()
    {
        $found = false;
        $runtime = new Handlebars\Runtime(false);
        $runtime->addHelper("foo", function(
            $bar, 
            $number,
            $something1, 
            $number2, 
            $something2,
            $options
        ) use (&$found) {
            $this->assertSame(4.5, $number);
            $this->assertSame(4, $number2);
            $this->assertSame('some"thi " ng', $something1);
            $this->assertSame("some'thi ' ng", $something2);
            $this->assertFalse($options['hash']['dog']);
            $this->assertSame('meow', $options['hash']['cat']);
            $this->assertSame('squeak squeak', $options['hash']['mouse']);
            $found = true;
            return $number2 + 1;
        });
        $handlebars = new Handlebars\Handlebars($runtime, call_user_func($this->compilerFactory, $runtime), $this->dataFactory);

        $template = $handlebars->compile(
            '{{foo 4bar4 4.5 \'some"thi " ng\' 4 "some\'thi \' ng" '
            .'dog=false cat="meow" mouse=\'squeak squeak\'}}');

        $result = $template(array("zoo" => "foobar"));
        $this->assertTrue($found);
        $this->assertSame("5", $result);
    }

    /**
     * @expectedException TypeError
     * @expectedExceptionMessage Argument 2 passed to MattyG\Handlebars\Runtime::addHelper() must be callable, integer given
     */
    public function testAddNonCallableHelper()
    {
        $runtime = new Handlebars\Runtime(false);
        $runtime->addHelper("foo", 1);
    }

    public function testGetPartial()
    {
        $runtime = new Handlebars\Runtime();
        $runtime->addPartial("foo", "bar");

        $this->assertTrue(is_string($runtime->getPartial("foo")));
        $this->assertNull($runtime->getPartial("foobar"));
    }

    public function testAddPartial1()
    {
        $runtime = new Handlebars\Runtime();
        $runtime->addPartial("foo", "This is {{ foo }}");
        $runtime->addPartial("bar", "Foo is not {{ bar }}");
        $handlebars = new Handlebars\Handlebars($runtime, call_user_func($this->compilerFactory, $runtime), $this->dataFactory);

        $template = $handlebars->compile("{{> foo }} ... {{> bar }}");

        $result = $template->render(array("foo" => "FOO", "bar" => "BAR"));
        $this->assertSame("This is FOO ... Foo is not BAR", $result);
    }

    public function testAddPartial2()
    {
        $runtime = new Handlebars\Runtime();
        $runtime->addPartial("foo", "This is {{ foo }}");
        $runtime->addPartial("bar", "Foo is not {{ bar }}");
        $handlebars = new Handlebars\Handlebars($runtime, call_user_func($this->compilerFactory, $runtime), $this->dataFactory);

        $template = $handlebars->compile("{{> foo }} ... {{> bar zoo}}");

        $result = $template->render(array("foo" => "FOO", "bar" => "BAR", "zoo" => array("bar" => "ZOO")));
        $this->assertSame("This is FOO ... Foo is not ZOO", $result);
    }

    public function testAddPartial3()
    {
        $runtime = new Handlebars\Runtime();
        $runtime->addPartial("foo", "This is {{ foo }}");
        $runtime->addPartial("bar", "Foo is not {{ something }}");
        $handlebars = new Handlebars\Handlebars($runtime, call_user_func($this->compilerFactory, $runtime), $this->dataFactory);

        $template = $handlebars->compile("{{> foo }} ... {{> bar zoo something='Amazing'}}");

        $result = $template->render(array("foo" => "FOO", "bar" => "BAR", "zoo" => array("bar" => "ZOO")));
        $this->assertSame("This is FOO ... Foo is not Amazing", $result);
    }
}
