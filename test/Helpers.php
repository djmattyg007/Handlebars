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

class Helpers extends TestCase
{
    /**
     * @var Handlebars\Handlebars
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

    public function testEach()
    {
        $loop1 = '{{#each comments}}{{{this}}}{{/each}}';
        $loop2 = '{{#each posts}}{{@index}}' . $loop1 . '{{/each}}';
        $loop3 = '{{#each users}}{{@index}}' . $loop2 . '{{/each}}';
        $loop4 = '{{foo}}{{#each comments}}{{../foo}}{{{this}}}{{/each}}';
        $loop5 = '{{#each posts}}{{@index}}' . $loop4 . '{{/each}}';
        $loop6 = '{{#each users}}{{@index}}' . $loop5 . '{{/each}}';
        $data1 = array('comments' => array(1, 2, 3, 4));
        $data2 = array('posts' => array($data1, $data1, $data1));
        $data3 = array_merge($data1, array('foo' => 'bar'));
        $data4 = array('posts' => array($data3, $data3, $data3));

        $cases = array(
            "simple loop" => array(
                $loop1,
                $data1,
                '1234',
            ),
            "nested loop 1" => array(
                $loop2,
                $data2,
                '012341123421234',
            ),
            "nested loop 2" => array(
                $loop3,
                array('users' => array($data2, $data2, $data2)),
                '001234112342123410123411234212342012341123421234',
            ),
            "nested loop 3 ../" => array(
                str_replace('{{{this}}}', '{{../../@index}}', $loop3),
                array('users' => array($data2, $data2, $data2)),
                '000000100002000010111111111211112022221222222222',
            ),
            "nested loop 3 ../ no results" => array(
                str_replace('{{{this}}}', '{{../..nada}}', $loop3),
                array('users' => array($data2, $data2, $data2)),
                '001210122012',
            ),
            "simple loop ../" => array(
                $loop4,
                $data3,
                'barbar1bar2bar3bar4',
            ),
            "nested loop 1 ../" => array(
                $loop5,
                $data4,
                '0barbar1bar2bar3bar41barbar1bar2bar3bar42barbar1bar2bar3bar4',
            ),
            "nested loop 2 ../" => array(
                $loop6,
                array('users' => array($data4, $data4, $data4)),
                '00barbar1bar2bar3bar41barbar1bar2bar3bar42barbar1bar2bar3bar410barbar' .
                '1bar2bar3bar41barbar1bar2bar3bar42barbar1bar2bar3bar420barbar1bar2bar' .
                '3bar41barbar1bar2bar3bar42barbar1bar2bar3bar4',
            ),
            "nested loop 3 ../../" => array(
                str_replace('{{{this}}}', '{{../../@index}}', $loop6),
                array('users' => array($data4, $data4, $data4)),
                '00barbar0bar0bar0bar01barbar0bar0bar0bar02barbar' .
                '0bar0bar0bar010barbar1bar1bar1bar11barbar1bar1bar' .
                '1bar12barbar1bar1bar1bar120barbar2bar2bar2bar21bar' .
                'bar2bar2bar2bar22barbar2bar2bar2bar2',
            ),
            "nested loop 3 ../../ no results" => array(
                str_replace('{{{this}}}', '{{../../nada}}', $loop6),
                array('users' => array($data4, $data4, $data4)),
                '00barbarbarbarbar1barbarbarbarbar2barbarbarbarbar10barbarbar' .
                'barbar1barbarbarbarbar2barbarbarbarbar20barbarbarbarbar1barbar' .
                'barbarbar2barbarbarbarbar',
            ),
            "each else" => array(
                '{{#each comments}}{{{this}}}{{else}}NO{{/each}}',
                array(),
                'NO',
            ),
            "private" => array(
                '{{#each comments}}{{@index}}{{@key}}{{@first}}{{@last}}{{this}}{{/each}}',
                array('comments' => array(1, 2, 3, 4)),
                '00031110322203333034',
            ),
            "foreach" => array(
                '{{#each comments as |value, key|}}{{key}}:{{value}},{{/each}}',
                array('comments' => array(1, 2, 3, 4)),
                '0:1,1:2,2:3,3:4,',
            ),
            "foreach value only" => array(
                '{{#each comments as |value|}}{{value}},{{/each}}',
                array('comments' => array(1, 2, 3, 4)),
                '1,2,3,4,',
            ),
        );

        foreach ($cases as $case) {
            $template = $this->handlebars->compile($case[0]);
            $results = $template($case[1]);
            $this->assertEquals($case[2], $results);
        }
    }

    public function testIf()
    {
        $cases = array(
            "simple if" => array(
                '{{#if comments}}YES{{else}}NO{{/if}}',
                array('comments' => array(1, 2, 3, 4)),
                'YES',
            ),
            "simple else" => array(
                '{{#if comments}}YES{{else}}NO{{/if}}',
                array(),
                'NO',
            ),
            "nested if" => array(
                '{{#if foo}}{{#if bar}}{{foo}} {{bar}}{{/if}}{{foo}}{{/if}}',
                array('foo' => 'bar', 'bar' => 'foo'),
                'bar foobar',
            ),
        );

        foreach ($cases as $case) {
            $template = $this->handlebars->compile($case[0]);
            $results = $template($case[1]);
            $this->assertEquals($case[2], $results);
        }
    }

    public function testUnless()
    {
        $cases = array(
            "simple unless" => array(
                '{{#unless comments}}YES{{else}}NO{{/unless}}',
                array('comments' => array(1, 2, 3, 4)),
                'NO',
            ),
            "simple else" => array(
                '{{#unless comments}}YES{{else}}NO{{/unless}}',
                array(),
                'YES',
            ),
        );

        foreach ($cases as $case) {
            $template = $this->handlebars->compile($case[0]);
            $results = $template($case[1]);
            $this->assertEquals($case[2], $results);
        }
    }

    public function testWith()
    {
        $cases = array(
            "simple with" => array(
                '{{#with comments}}{{this.[1]}}{{/with}}',
                array('comments' => array(1, 2, 3, 4)),
                '2',
            ),
        );

        foreach ($cases as $case) {
            $template = $this->handlebars->compile($case[0]);
            $results = $template($case[1]);
            $this->assertEquals($case[2], $results);
        }
    }

    public function testConcat()
    {
        $cases = array(
            "two string literals" => array(
                '{{concat "foo" "bar"}}',
                array(),
                'foobar',
            ),
            "five string literals" => array(
                '{{concat "a" \'b\' "c" \'d\' "e"}}',
                array(),
                'abcde',
            ),
            "string literal plus variable" => array(
                '{{concat "abc" xyz}}',
                array('xyz' => 'def'),
                'abcdef',
            ),
            "two variables plus literal" => array(
                '{{concat person.firstname " " person.lastname}}',
                array('person' => array('firstname' => 'John', 'lastname' => 'Smith')),
                'John Smith',
            ),
            "single item" => array(
                '{{concat \'  __  \'}}',
                array(),
                '  __  ',
            ),
            "nested concat" => array(
                '{{concat "abc" (concat def "ghi") "jkl"}}',
                array('def' => 'def'),
                'abcdefghijkl',
            ),
            "open nested concat" => array(
                '{{#if (concat "abc" "def")}}test{{/if}}',
                array(),
                'test',
            ),
            "double nested concat" => array(
                '{{concat abc (concat "def" ( concat ghi "jkl")) "mno"}}',
                array('abc' => 'abc', 'ghi' => 'ghi'),
                'abcdefghijklmno',
            ),
            "many nested concat" => array(
                '{{ concat (concat   "abc"  (  concat "def" ghi   (concat "jkl" ) ) (concat "mno" "pqr")  )   (concat "stu" "vw" (concat xyz) ) "test"   }}',
                array('ghi' => 'ghi', 'xyz' => 'xyz'),
                'abcdefghijklmnopqrstuvwxyztest',
            ),
        );

        foreach ($cases as $case) {
            $template = $this->handlebars->compile($case[0]);
            $results = $template($case[1]);
            $this->assertEquals($case[2], $results);
        }
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

        $template = $this->handlebars->compile($contents);
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
