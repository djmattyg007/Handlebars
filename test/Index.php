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

class Eden_Handlebars_Index_Test extends PHPUnit_Framework_TestCase
{
    public function testCompile() 
    {
        $template = eden('handlebars')->compile('{{foo}}{{{foo}}}');
        
        $results = $template(array('foo' => '<strong>foo</strong>'));
        
        $this->assertEquals('&lt;strong&gt;foo&lt;/strong&gt;<strong>foo</strong>', $results); 
    }

    public function testSetAndGetCache()
    {
        $this->assertNull(eden('handlebars')->getCachePath());
        $this->assertEquals('/foo/bar', eden('handlebars')->setCachePath('/foo/bar')->getCachePath());
    }

    public function testGetHelper()
    {
        $this->assertTrue(is_callable(eden('handlebars')->getHelper('if')));
        $this->assertNull(eden('handlebars')->getHelper('foobar'));
    }

    public function testGetHelpers()
    {
        $helpers = eden('handlebars')->getHelpers();
        $this->assertTrue(is_array($helpers));
    }

    public function testGetPartial()
    {
        $this->assertTrue(is_string(eden('handlebars')->registerPartial('foo', 'bar')->getPartial('foo')));
        $this->assertNull(eden('handlebars')->getPartial('foobar'));
    }

    public function testGetPartials()
    {
        $partials = eden('handlebars')->getPartials();

        $this->assertTrue(is_array($partials));
    }

    public function testRegisterHelper() 
    {
        $handlebars = eden('handlebars');

        //simple helper
        $handlebars->registerHelper('root', function() {
            return '/some/root';
        });

        $template = $handlebars->compile('{{root}}/bower_components/eve-font-awesome/awesome.css');

        $results = $template();
        $this->assertEquals('/some/root/bower_components/eve-font-awesome/awesome.css', $results); 

        $found = false;
        $self = $this;
        $template = eden('handlebars')
            ->registerHelper('foo', function(
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
            })
            ->compile('{{foo bar 4 true null false zoo}}');

        $results = $template(array('zoo' => 'foobar'));
        $this->assertTrue($found);
        $this->assertEquals(5, $results); 

        $found = false;
        $template = eden('handlebars')
            ->registerHelper('foo', function(
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
            })
            ->compile('{{{foo 4.5 \'some"thi " ng\' 4 "some\'thi \' ng"}}}');

        $results = $template();

        $this->assertTrue($found);
        $this->assertEquals('some"thi " ng'."some'thi ' ng", $results); 

        //attributes test
        $found = false;
        $template = eden('handlebars')
            ->registerHelper('foo', function(
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
            })
            ->compile(
                '{{foo 4bar4 4.5 \'some"thi " ng\' 4 "some\'thi \' ng" '
                .'dog=false cat="meow" mouse=\'squeak squeak\'}}');

        $results = $template(array('zoo' => 'foobar'));
        $this->assertTrue($found);
        $this->assertEquals(5, $results);
    }

    public function testRegisterPartial()
    {
        //basic
        $template = eden('handlebars')
            ->registerPartial('foo', 'This is {{ foo }}')
            ->registerPartial('bar', 'Foo is not {{ bar }}')
            ->compile('{{> foo }} ... {{> bar }}');

        $results = $template(array('foo' => 'FOO', 'bar' => 'BAR'));

        $this->assertEquals('This is FOO ... Foo is not BAR', $results); 

        //with scope
        $template = eden('handlebars')
            ->registerPartial('foo', 'This is {{ foo }}')
            ->registerPartial('bar', 'Foo is not {{ bar }}')
            ->compile('{{> foo }} ... {{> bar zoo}}');

        $results = $template(array('foo' => 'FOO', 'bar' => 'BAR', 'zoo' => array('bar' => 'ZOO')));

        $this->assertEquals('This is FOO ... Foo is not ZOO', $results); 

        //with attributes
        $template = eden('handlebars')
            ->registerPartial('foo', 'This is {{ foo }}')
            ->registerPartial('bar', 'Foo is not {{ something }}')
            ->compile('{{> foo }} ... {{> bar zoo something="Amazing"}}');

        $results = $template(array('foo' => 'FOO', 'bar' => 'BAR', 'zoo' => array('bar' => 'ZOO')));

        $this->assertEquals('This is FOO ... Foo is not Amazing', $results);
    }

    public function testSetCacheFilePrefix()
    {
        $instance = eden('handlebars')->setCacheFilePrefix('foobar');
        $this->assertInstanceOf('Eden\\Handlebars\\Index', $instance);
    }

    public function testUnregisterHelper()
    {
        $instance = eden('handlebars')->unregisterHelper('if');
        $this->assertInstanceOf('Eden\\Handlebars\\Index', $instance);

        $this->assertNull($instance->getHelper('if'));
    }

    public function testUnregisterPartial()
    {
        $instance = eden('handlebars')
            ->registerPartial('foo', 'bar')
            ->unregisterPartial('foo');

        $this->assertInstanceOf('Eden\\Handlebars\\Index', $instance);

        $this->assertNull($instance->getPartial('foo'));
    }
}
