<?php //-->
/**
 * This file is part of the Eden PHP Library.
 * (c) 2014-2016 Openovate Labs
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */
class EdenHandlebarshelpersTest extends PHPUnit_Framework_TestCase
{
	public function setUp() {
		//reset the helpers and partials after every test
		eden('handlebars')->reset();
	}
	
	public function testEach()
	{
		//simple loop
		$case1 = array(
			'{{#each comments}}{{{this}}}{{/each}}',
			array('comments' => array(1, 2, 3, 4)),
			'1234'
		);
		
		//nested loop 1
		$case2 = array(
			'{{#each posts}}{{@index}}'.$case1[0].'{{/each}}',
			array('posts' => array($case1[1], $case1[1], $case1[1])),
			'012341123421234'
		);
		
		//nested loop 2
		$case3 = array(
			'{{#each users}}{{@index}}'.$case2[0].'{{/each}}',
			array('users' => array($case2[1], $case2[1], $case2[1])),
			'001234112342123410123411234212342012341123421234'
		);
		
		//nested loop 3 ../
		$case4 = array(
			str_replace('{{{this}}}', '{{../../@index}}', $case3[0]),
			array('users' => array($case2[1], $case2[1], $case2[1])),
			'000000100002000010111111111211112022221222222222'
		);
		
		//nested loop 3 ../ no results
		$case5 = array(
			str_replace('{{{this}}}', '{{../../nada}}', $case3[0]),
			array('users' => array($case2[1], $case2[1], $case2[1])),
			'001210122012'
		);
		
		//simple loop ../
		$case6 = array(
			'{{foo}}{{#each comments}}{{../foo}}{{{this}}}{{/each}}',
			array('foo' => 'bar', 'comments' => array(1, 2, 3, 4)),
			'barbar1bar2bar3bar4'
		);
		
		//nested loop 1 ../
		$case7 = array(
			'{{#each posts}}{{@index}}'.$case6[0].'{{/each}}',
			array('posts' => array($case6[1], $case6[1], $case6[1])),
			'0barbar1bar2bar3bar41barbar1bar2bar3bar42barbar1bar2bar3bar4'
		);
		
		//nested loop 2 ../
		$case8 = array(
			'{{#each users}}{{@index}}'.$case7[0].'{{/each}}',
			array('users' => array($case7[1], $case7[1], $case7[1])),
			'00barbar1bar2bar3bar41barbar1bar2bar3bar42barbar1bar2bar3bar410barbar'.
			'1bar2bar3bar41barbar1bar2bar3bar42barbar1bar2bar3bar420barbar1bar2bar'.		
			'3bar41barbar1bar2bar3bar42barbar1bar2bar3bar4'
		);
		
		//nested loop 3 ../../
		$case9 = array(
			str_replace('{{{this}}}', '{{../../@index}}', $case8[0]),
			array('users' => array($case7[1], $case7[1], $case7[1])),
			'00barbar0bar0bar0bar01barbar0bar0bar0bar02barbar'.
			'0bar0bar0bar010barbar1bar1bar1bar11barbar1bar1bar'.
			'1bar12barbar1bar1bar1bar120barbar2bar2bar2bar21bar'.
			'bar2bar2bar2bar22barbar2bar2bar2bar2'
		);
		
		//nested loop 3 ../../ no results
		$case10 = array(
			str_replace('{{{this}}}', '{{../../nada}}', $case8[0]),
			array('users' => array($case7[1], $case7[1], $case7[1])),
			'00barbarbarbarbar1barbarbarbarbar2barbarbarbarbar10barbarbar'.
			'barbar1barbarbarbarbar2barbarbarbarbar20barbarbarbarbar1barbar'.
			'barbarbar2barbarbarbarbar'
		);
		
		//each else
		$case11 = array(
			'{{#each comments}}{{{this}}}{{else}}NO{{/each}}',
			array(),
			'NO'
		);
		
		//private
		$case12 = array(
			'{{#each comments}}{{@index}}{{@key}}{{@first}}{{@last}}{{this}}{{/each}}',
			array('comments' => array(1, 2, 3, 4)),
			'00031110322203333034'
		);
		
		//foreach
		$case13 = array(
			'{{#each comments as |value, key|}}{{key}}:{{value}},{{/each}}',
			array('comments' => array(1, 2, 3, 4)),
			'0:1,1:2,2:3,3:4,'
		);
		
		$case14 = array(
			'{{#each comments as |value|}}{{value}},{{/each}}',
			array('comments' => array(1, 2, 3, 4)),
			'1,2,3,4,'
		);
		
		$template = eden('handlebars')->compile($case1[0]);
		$results = $template($case1[1]);
		$this->assertEquals($case1[2], $results); 
		
		$template = eden('handlebars')->compile($case2[0]);
		$results = $template($case2[1]);
		$this->assertEquals($case2[2], $results); 
		
		$template = eden('handlebars')->compile($case3[0]);
		$results = $template($case3[1]);
		$this->assertEquals($case3[2], $results); 
		
		$template = eden('handlebars')->compile($case4[0]);
		$results = $template($case4[1]);
		$this->assertEquals($case4[2], $results); 
		
		$template = eden('handlebars')->compile($case5[0]);
		$results = $template($case5[1]);
		$this->assertEquals($case5[2], $results); 
		
		$template = eden('handlebars')->compile($case6[0]);
		$results = $template($case6[1]);
		$this->assertEquals($case6[2], $results); 
		
		$template = eden('handlebars')->compile($case7[0]);
		$results = $template($case7[1]);
		$this->assertEquals($case7[2], $results); 
		
		$template = eden('handlebars')->compile($case8[0]);
		$results = $template($case8[1]);
		$this->assertEquals($case8[2], $results); 
		
		$template = eden('handlebars')->compile($case9[0]);
		$results = $template($case9[1]);
		$this->assertEquals($case9[2], $results); 
		
		$template = eden('handlebars')->compile($case10[0]);
		$results = $template($case10[1]);
		$this->assertEquals($case10[2], $results); 
		
		$template = eden('handlebars')->compile($case11[0]);
		$results = $template($case11[1]);
		$this->assertEquals($case11[2], $results); 
		
		$template = eden('handlebars')->compile($case12[0]);
		$results = $template($case12[1]);
		$this->assertEquals($case12[2], $results); 
		
		$template = eden('handlebars')->compile($case13[0]);
		$results = $template($case13[1]);
		$this->assertEquals($case13[2], $results); 
		
		$template = eden('handlebars')->compile($case14[0]);
		$results = $template($case14[1]);
		$this->assertEquals($case14[2], $results); 
	}
	
	public function testIf()
	{
		//simple if
		$case1 = array(
			'{{#if comments}}YES{{else}}NO{{/if}}',
			array('comments' => array(1, 2, 3, 4)),
			'YES'
		);
		
		//simple else
		$case2 = array(
			'{{#if comments}}YES{{else}}NO{{/if}}',
			array(),
			'NO'
		);
		
		//nested if
		$case3 = array(
			'{{#if foo}}{{#if bar}}{{foo}} {{bar}}{{/if}}{{foo}}{{/if}}',
			array('foo' => 'bar', 'bar' => 'foo'),
			'bar foobar'
		);
		
		$template = eden('handlebars')->compile($case1[0]);
		$results = $template($case1[1]);
		$this->assertEquals($case1[2], $results); 
		
		$template = eden('handlebars')->compile($case2[0]);
		$results = $template($case2[1]);
		$this->assertEquals($case2[2], $results); 
		
		$template = eden('handlebars')->compile($case3[0]);
		$results = $template($case3[1]);
		$this->assertEquals($case3[2], $results); 
	}
	
	public function testUnless()
	{
		//simple unless
		$case1 = array(
			'{{#unless comments}}YES{{else}}NO{{/unless}}',
			array('comments' => array(1, 2, 3, 4)),
			'NO'
		);
		
		//simple else
		$case2 = array(
			'{{#unless comments}}YES{{else}}NO{{/unless}}',
			array(),
			'YES'
		);
		
		$template = eden('handlebars')->compile($case1[0]);
		$results = $template($case1[1]);
		$this->assertEquals($case1[2], $results); 
		
		$template = eden('handlebars')->compile($case2[0]);
		$results = $template($case2[1]);
		$this->assertEquals($case2[2], $results); 
	}
	
	public function testWith()
	{
		//simple with
		$case1 = array(
			'{{#with comments}}{{this.[1]}}{{/with}}',
			array('comments' => array(1, 2, 3, 4)),
			'2'
		);
		
		$template = eden('handlebars')->compile($case1[0]);
		$results = $template($case1[1]);
		$this->assertEquals($case1[2], $results); 
	}
	
	public function testCombinations()
	{
		$contents = '{{#if merchants.length~}}
			{{{query.profile_id~}}}
			{{#each merchants~}}
				{{profile_name~}}
				{{#if ../query.profile_id.length~}}
					Yes1
				{{~else~}}
					{{#if ../query.profile_name.length~}}
						{{!-- This Works --~}}
						Yes2
					{{~else~}}
						NO2
						{{~query.profile_id~}}
					{{/if~}}
				{{/if~}}
			{{/each~}}
		{{else~}}
		NO1
		{{~/if}}';
				
		$template = eden('handlebars')->compile($contents);
		$results = $template(array(
			'merchants' => array(
				array('profile_name' => 'John'),
				array('profile_name' => 'Jane'),
				array('profile_name' => 'Jill')
			),
			'query' => array('profile_id' => '123', 'profile_name' => 'John')
		));
		
		$this->assertEquals('123JohnYes1JaneYes1JillYes1', $results); 
		
	}
}