<?php //-->
/*
 * This file is part of the Utility package of the Eden PHP Library.
 * (c) 2013-2014 Openovate Labs
 *
 * Copyright and license information can be found at LICENSE
 * distributed with this package.
 */
class EdenHandlebarsfunctionalTest extends PHPUnit_Framework_TestCase
{	
	//functional tests
	public function testLiterally()
	{
		//helper vs property
		$template = eden('handlebars')
			->registerHelper('zoo', function($float) {
				return $float + 1;
			})
			->compile('{{zoo 4.5}} {{./zoo}}');
		
		$results = $template(array('zoo' => 'foobar'));
		$this->assertEquals('5.5 foobar', $results); 
		
		//helper vs property rd2
		$template = eden('handlebars')
			->registerHelper('query', function($keyword) {
				return 'foobar';
			})
			->compile('{{query \'keyword\'}} {{query.keyword}}');
		
		$results = $template(array('query' => array('keyword' => 'foobar')));
		$this->assertEquals('foobar foobar', $results); 
	}
	
	public function testTrim()
	{
		$template = eden('handlebars')
			->registerHelper('zoo', function($float, $options) {
				return $options['fn']();
			})
			->compile(' {{~#zoo 4.5~}} 456 {{~/zoo~}} ');
		
		$results = $template();
		$this->assertEquals('456', $results); 
		
		$template = eden('handlebars')
			->compile('{{zoo}} {{~#each foo~}} 456 {{~bar}} {{/each~}} ');
		
		$results = $template(array(
			'zoo' => 4,
			'foo' => array(
				array('bar' => 'a'),
				array('bar' => 'b'),
				array('bar' => 'c'),
				array('bar' => 'd'),
				array('bar' => 'e'),
			)
		));
		
		$this->assertEquals('4456a 456b 456c 456d 456e ', $results); 
	}
	
	public function testComment()
	{
		$template = eden('handlebars')->compile('{{!-- Some Comment --}}{{foo}}');
		
		$results = $template(array('foo' => 'bar'));
		
		$this->assertEquals('bar', $results); 
		
		$template = eden('handlebars')->compile('{{! Some Comment }}{{foo}}');
		
		$results = $template(array('foo' => 'bar'));
		
		$this->assertEquals('bar', $results); 
	}
	
	public function testUnknownTrigger()
	{
		$template = eden('handlebars')->on('handlebars-unknown', function($name, $args) {
			$args = $this->parseArguments($args);
			
			$this->registerHelper($name, function($value) {
				return $value + 1;
			});
		})->compile('{{unknown_test 4 "foo bar"}}');
		
		$results = $template();
		
		$this->assertEquals(5, $results);
	}
	
	public function testLength()
	{
		$template = eden('handlebars')->compile('{{foo.length}}');
		
		$results = $template(array('foo' => array(1, 2, 3)));
		
		$this->assertEquals(3, $results); 
	}
}