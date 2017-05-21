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
use MattyG\Handlebars\Helper\LogHelper;
use PHPUnit\Framework\TestCase;
use Psr\Log\AbstractLogger;
use Psr\Log\InvalidArgumentException;
use Psr\Log\LogLevel;

class HelpersTest extends TestCase
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
            $this->assertSame($case[2], $results);
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
            $this->assertSame($case[2], $results);
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
            $this->assertSame($case[2], $results);
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
            $this->assertSame($case[2], $results);
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
            $this->assertSame($case[2], $results);
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

        $this->assertSame('123JohnYes1JaneYes1JillYes1', $results);
    }

    /**
     * @dataProvider logHelperProvider
     */
    public function testLog(string $contents, array $context, string $expectedTemplateResult, array $logCalls)
    {
        $logger = $this->prophesize(AbstractLogger::class);
        foreach ($logCalls as $logCall) {
            $logger->log($logCall["level"], $logCall["message"], $logCall["context"] ?? array())->shouldBeCalledTimes($logCall["calls"]);
        }

        $logHelper = new LogHelper($logger->reveal());
        $this->handlebars->registerHelper("log", $logHelper);

        $template = $this->handlebars->compile($contents);
        $result = $template($context);

        $this->assertSame($expectedTemplateResult, $result);
    }

    /**
     * @return array
     */
    public function logHelperProvider()
    {
        // template contents, template result, log level, log message, log context
        return array(
            array('abc{{log "Test Number One" level="warning"}}def', array(), "abcdef", array(
                array("level" => LogLevel::WARNING, "message" => "Test Number One", "calls" => 1),
            )),
            array('123{{log "Test Number Two" level="warn" context1="yes we can" context2="no we can\'t"}}456', array(), "123456", array(
                array("level" => LogLevel::WARNING, "message" => "Test Number Two", "calls" => 1, "context" => array("context1" => "yes we can", "context2" => "no we can't")),
            )),
            array('{{log "Test Three" level=loglevel1 context3=person.name}}{{log person.street}}', array("loglevel1" => "emergency", "person" => array("name" => "John Citizen", "street" => "123 Test Street")), "", array(
                array("level" => LogLevel::EMERGENCY, "message" => "Test Three", "calls" => 1, "context" => array("context3" => "John Citizen")),
                array("level" => LogLevel::INFO, "message" => "123 Test Street", "calls" => 1),
            )),
        );
    }

    /**
     * @expectedException Psr\Log\InvalidArgumentException
     * @expectedExceptionMessage Unknown log level 'emerg' specified.
     */
    public function testLogWithUnknownLevel()
    {
        $logger = $this->getMockForAbstractClass(AbstractLogger::class);
        $logHelper = new LogHelper($logger);
        $this->handlebars->registerHelper("log", $logHelper);

        $contents = '{{log "Test Log Message" level="emerg"}}';
        $template = $this->handlebars->compile($contents);
        $template();
    }
}
