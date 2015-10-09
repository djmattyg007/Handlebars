<?php //-->
/*
 * This file is part of the Utility package of the Eden PHP Library.
 * (c) 2013-2014 Openovate Labs
 *
 * Copyright and license information can be found at LICENSE
 * distributed with this package.
 */
class EdenHandlebarsIndexTest extends PHPUnit_Framework_TestCase
{
	public function testCompile() 
    {
		$template = eden('handlebars')->compile('{{foo}}{{{foo}}}');
		
		$results = $template(array('foo' => '<strong>foo</strong>'));
		
		$this->assertEquals('&lt;strong&gt;foo&lt;/strong&gt;<strong>foo</strong>', $results); 
	}
	
	public function testGetContext()
	{
		$context = eden('handlebars')->getContext();
		
		$this->assertInstanceOf('Eden\\Handlebars\\Context', $context);
	}
	
	public function testGetEngine()
	{
		$engine = eden('handlebars')->getEngine();
		
		$this->assertInstanceOf('Eden\\Handlebars\\Engine', $engine);
	}
	
	public function testGetOptions()
	{
		$options = eden('handlebars')->getOptions();
		
		$this->assertTrue(is_array($options));
		$this->assertTrue(is_array($options['hash']));
		
		$this->assertTrue(is_string($options['source']));
		$this->assertTrue(is_string($options['success']));
		$this->assertTrue(is_string($options['fail']));
		$this->assertTrue(is_string($options['args']));
		
		$this->assertTrue(is_callable($options['fn']));
		$this->assertTrue(is_callable($options['inverse']));
		$this->assertTrue(is_callable($options['helper']));
		
		$this->assertInstanceOf('Eden\\Handlebars\\Index', $options['handlebars']);
		$this->assertInstanceOf('Mustache_LambdaHelper', $options['lambda']);
		
	}
	
	public function testGetPartials()
	{
		$partials = eden('handlebars')->getPartials();
		
		$this->assertTrue(is_array($partials));
	}
	
	public function testGetTemplate()
	{
		$template = eden('handlebars')->getTemplate();
		
		$this->assertNull($template);
	}
	
	public function testParseArguments()
	{
		$args = eden('handlebars')->parseArguments(
			'4bar4 4.5 \'some"thi " ng\' 4 "some\'thi \' ng" '
			.'dog=false cat="meow" mouse=\'squeak squeak\'');
		
		$this->assertEquals('', $args[0][0]);
		$this->assertEquals(4.5, $args[0][1]);
		$this->assertEquals('some"thi " ng', $args[0][2]);
		$this->assertEquals(4, $args[0][3]);
		$this->assertEquals("some'thi ' ng", $args[0][4]);
		$this->assertFalse($args[1]['dog']);
		$this->assertEquals('meow', $args[1]['cat']);
		$this->assertEquals('squeak squeak', $args[1]['mouse']);
	}
	
    public function testRegisterHelper() 
    {
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
	
	public function testSetContext()
	{
		$handlebars = eden('handlebars')->setContext(array('Foo' => 'Bar'));
		$this->assertInstanceOf('Eden\\Handlebars\\Index', $handlebars);
			
		$handlebars = eden('handlebars')->setContext(new Eden\Handlebars\Context(array('Foo' => 'Bar')));
		$this->assertInstanceOf('Eden\\Handlebars\\Index', $handlebars);
	}
	
	public function testSetEngine()
	{
		$handlebars = eden('handlebars')->setEngine(new Eden\Handlebars\Engine);
		$this->assertInstanceOf('Eden\\Handlebars\\Index', $handlebars);
	}
	
	public function testSetTemplate()
	{
		$handlebars = eden('handlebars')->setTemplate('foo bar');
		$this->assertInstanceOf('Eden\\Handlebars\\Index', $handlebars);
	}
}