<?php
declare(strict_types=1);
/**
 * This file is part of the Eden PHP Library.
 * (c) 2014-2016 Openovate Labs
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

use Eden\Handlebars;
 
/**
 * The following tests were pulled from the Mustache.php Library
 * and kept as is with changes to the final class name to test 
 * backwards compatibility
 */
class Eden_Handlebars_Runtime_Test extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        //reset the helpers and partials after every test
        eden('handlebars')->reset();
    }

    public function testGetHelper()
    {
        Handlebars\Runtime::registerHelper('foo', function() {});
        $this->assertInstanceOf('Closure', Handlebars\Runtime::getHelper('foo'));
        $this->assertNull(Handlebars\Runtime::getHelper('bar'));
    }

    public function testGetHelpers()
    {
        $helpers = Handlebars\Runtime::getHelpers();
        $this->assertTrue(is_array($helpers));
    }

    public function testGetPartial()
    {
        Handlebars\Runtime::registerPartial('foo', 'bar');
        $this->assertTrue(is_string(Handlebars\Runtime::getPartial('foo')));
        $this->assertNull(Handlebars\Runtime::getPartial('foobar'));
    }

    public function testGetPartials()
    {
        $partials = Handlebars\Runtime::getPartials();

        $this->assertTrue(is_array($partials));
    }

    public function testRegisterHelper() 
    {
        //simple helper
        Handlebars\Runtime::registerHelper('root', function() {
            return '/some/root';
        });

        $template = eden('handlebars')->compile('{{root}}/bower_components/eve-font-awesome/awesome.css');

        $results = $template();
        $this->assertEquals('/some/root/bower_components/eve-font-awesome/awesome.css', $results); 

        $found = false;
        $self = $this;
        Handlebars\Runtime::registerHelper('foo', function(
            $bar, 
            $four, 
            $true, 
            $null, 
            $false,
            $zoo
        ) use ($self, &$found) {
            $self->assertEquals('', $bar);
            $self->assertEquals(4, $four);
            $self->assertTrue($true);
            $self->assertNull($null);
            $self->assertFalse($false);
            $self->assertEquals('foobar', $zoo);
            $found = true;
            return $four + 1;
        });

        $template = eden('handlebars')->compile('{{foo bar 4 true null false zoo}}');

        $results = $template(array('zoo' => 'foobar'));
        $this->assertTrue($found);
        $this->assertEquals(5, $results); 

        $found = false;
        Handlebars\Runtime::registerHelper('foo', function(
            $number, 
            $something1, 
            $number2, 
            $something2
        ) use ($self, &$found) {
            $self->assertEquals(4.5, $number);
            $self->assertEquals(4, $number2);
            $self->assertEquals('some"thi " ng', $something1);
            $self->assertEquals("some'thi ' ng", $something2);
            $found = true;

            return $something1.$something2;
        });

        $template = eden('handlebars')->compile('{{{foo 4.5 \'some"thi " ng\' 4 "some\'thi \' ng"}}}');

        $results = $template();

        $this->assertTrue($found);
        $this->assertEquals('some"thi " ng'."some'thi ' ng", $results); 

        //attributes test
        $found = false;
        Handlebars\Runtime::registerHelper('foo', function(
            $bar, 
            $number,
            $something1, 
            $number2, 
            $something2,
            $options
        ) use ($self, &$found) {
            $self->assertEquals(4.5, $number);
            $self->assertEquals(4, $number2);
            $self->assertEquals('some"thi " ng', $something1);
            $self->assertEquals("some'thi ' ng", $something2);
            $self->assertFalse($options['hash']['dog']);
            $self->assertEquals('meow', $options['hash']['cat']);
            $self->assertEquals('squeak squeak', $options['hash']['mouse']);
            
            $found = true;
            return $number2 + 1;
        });

        $template = eden('handlebars')->compile(
            '{{foo 4bar4 4.5 \'some"thi " ng\' 4 "some\'thi \' ng" '
            .'dog=false cat="meow" mouse=\'squeak squeak\'}}');

        $results = $template(array('zoo' => 'foobar'));
        $this->assertTrue($found);
        $this->assertEquals(5, $results);
    }

    public function testRegisterPartial()
    {
        //basic
        Handlebars\Runtime::registerPartial('foo', 'This is {{ foo }}');
        Handlebars\Runtime::registerPartial('bar', 'Foo is not {{ bar }}');
        $template = eden('handlebars')->compile('{{> foo }} ... {{> bar }}');

        $results = $template(array('foo' => 'FOO', 'bar' => 'BAR'));

        $this->assertEquals('This is FOO ... Foo is not BAR', $results); 

        //with scope
        Handlebars\Runtime::registerPartial('foo', 'This is {{ foo }}');
        Handlebars\Runtime::registerPartial('bar', 'Foo is not {{ bar }}');
        $template = eden('handlebars')->compile('{{> foo }} ... {{> bar zoo}}');

        $results = $template(array('foo' => 'FOO', 'bar' => 'BAR', 'zoo' => array('bar' => 'ZOO')));

        $this->assertEquals('This is FOO ... Foo is not ZOO', $results); 

        //with attributes
        Handlebars\Runtime::registerPartial('foo', 'This is {{ foo }}');
        Handlebars\Runtime::registerPartial('bar', 'Foo is not {{ something }}');
        $template = eden('handlebars')->compile('{{> foo }} ... {{> bar zoo something="Amazing"}}');

        $results = $template(array('foo' => 'FOO', 'bar' => 'BAR', 'zoo' => array('bar' => 'ZOO')));

        $this->assertEquals('This is FOO ... Foo is not Amazing', $results);
    }

    public function testUnregisterHelper()
    {
        Handlebars\Runtime::unregisterHelper('if');
        $this->assertNull(Handlebars\Runtime::getHelper('if'));
    }

    public function testUnregisterPartial()
    {
        Handlebars\Runtime::registerPartial('foo', 'bar');
        Handlebars\Runtime::unregisterPartial('foo');
        $this->assertNull(Handlebars\Runtime::getPartial('foo'));
    }
}
