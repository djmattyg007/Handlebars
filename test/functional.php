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

class Functional extends \PHPUnit_Framework_TestCase
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

    public function testLiterally()
    {
        //helper vs property
        $this->handlebars->registerHelper('zoo', function($float) {
            return $float + 1;
        });
        $template = $this->handlebars->compile('{{zoo 4.5}} {{./zoo}}');

        $result = $template(array('zoo' => 'foobar'));
        $this->assertEquals('5.5 foobar', $result);

        //helper vs property rd2
        $this->handlebars->registerHelper('query', function($keyword) {
            return 'foobar';
        });
        $template = $this->handlebars->compile('{{query \'keyword\'}} {{query.keyword}}');

        $result = $template->render(array('query' => array('keyword' => 'foobar')));
        $this->assertEquals('foobar foobar', $result);
    }

    public function testTrim()
    {
        $this->handlebars->registerHelper('zoo', function($float, $options) {
            return $options['fn']();
        });

        $template = $this->handlebars->compile(' {{~#zoo 4.5~}} 456 {{~/zoo~}} ');
        $result = $template();
        $this->assertEquals('456', $result);

        $template = $this->handlebars->compile('{{bar}} {{~#each foo~}} 456 {{~bar}} {{/each~}} ');
        $result = $template(array(
            'bar' => 4,
            'foo' => array(
                array('bar' => 'a'),
                array('bar' => 'b'),
                array('bar' => 'c'),
                array('bar' => 'd'),
                array('bar' => 'e'),
            )
        ));
        $this->assertEquals('4456a 456b 456c 456d 456e ', $result);
    }

    public function testComment()
    {
        $template = $this->handlebars->compile('{{!-- Some Comment --}}{{foo}}');
        $result = $template(array('foo' => 'bar'));
        $this->assertEquals('bar', $result);

        $template = $this->handlebars->compile('{{! Some Comment }}{{foo}}');
        $result = $template(array('foo' => 'bar'));
        $this->assertEquals('bar', $result);
    }

    public function testLength()
    {
        $template = $this->handlebars->compile('{{foo.length}}');
        $result = $template(array('foo' => array(1, 2, 3)));
        $this->assertEquals(3, $result);
    }

    public function testMustache()
    {
        $template = $this->handlebars->compile('{{#foo}}{{this}}{{/foo}}');
        $result = $template(array('foo' => array(1, 2, 3)));
        $this->assertEquals('123', $result);
    }
}
