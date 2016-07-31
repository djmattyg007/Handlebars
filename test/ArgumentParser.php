<?php
declare(strict_types=1);

namespace MattyG\Handlebars\Test;

use MattyG\Handlebars;

class ArgumentParser extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Handlebars\ArgumentParserFactory
     */
    protected $argumentParserFactory;

    protected function setUp()
    {
        $this->argumentParserFactory = new Handlebars\ArgumentParserFactory();
    }

    protected function tearDown()
    {
        $this->argumentParserFactory = null;
    }

    public function testTokenise1()
    {
        $argParser = $this->argumentParserFactory->create("foobar 'merchant' query.profile_type");
        list($name, $args, $hash) = $argParser->tokenise();

        $this->assertEquals('foobar', $name);
        $this->assertCount(2, $args);
        $this->assertEquals("'merchant'", $args[0]);
        $this->assertEquals('$data->find(\'query.profile_type\')', $args[1]);
        $this->assertCount(0, $hash);
    }

    public function testTokenise2()
    {
        $argParser = $this->argumentParserFactory->create('foobarbaz 4bar4 4.5 \'some"thi " ng\' 4 "some\'thi \' ng" dog=false cat="meow" mouse=\'squeak squeak\'');
        list($name, $args, $hash) = $argParser->tokenise();

        $this->assertEquals('foobarbaz', $name);
        $this->assertCount(5, $args);
        $this->assertEquals('$data->find(\'4bar4\')', $args[0]);
        $this->assertEquals('4.5', $args[1]);
        $this->assertEquals("'some\"thi \" ng'", $args[2]);
        $this->assertEquals('4', $args[3]);
        $this->assertEquals("'some\'thi \' ng'", $args[4]);
        $this->assertCount(3, $hash);
        $this->assertEquals('false', $hash['dog']);
        $this->assertEquals("'meow'", $hash['cat']);
        $this->assertEquals("'squeak squeak'", $hash['mouse']);
    }

    public function testTokenise3()
    {
        $argParser = $this->argumentParserFactory->create('_ \'TODAY\\\'S\' BEST DEALS\'');
        list($name, $args, $hash) = $argParser->tokenise();

        $this->assertEquals('_', $name);
        $this->assertCount(3, $args);
        $this->assertEquals("'TODAY\\'S'", $args[0]);
        $this->assertEquals('$data->find(\'BEST\')', $args[1]);
        $this->assertEquals('$data->find(\'DEALS\\\'\')', $args[2]);
        $this->assertCount(0, $hash);
    }

    public function testTokenise4()
    {
        $argParser = $this->argumentParserFactory->create('abc x ');
        list($name, $args, $hash) = $argParser->tokenise();

        $this->assertEquals('abc', $name);
        $this->assertCount(1, $args);
        $this->assertEquals('$data->find(\'x\')', $args[0]);
        $this->assertCount(0, $hash);
    }

    public function testTokenise5()
    {
        $argParser = $this->argumentParserFactory->create('___   a  "b"    cd   hash=hashed');
        list($name, $args, $hash) = $argParser->tokenise();

        $this->assertEquals('___', $name);
        $this->assertCount(3, $args);
        $this->assertEquals('$data->find(\'a\')', $args[0]);
        $this->assertEquals("'b'", $args[1]);
        $this->assertEquals('$data->find(\'cd\')', $args[2]);
        $this->assertCount(1, $hash);
        $this->assertEquals('$data->find(\'hashed\')', $hash['hash']);
    }

    public function testTokenise6()
    {
        $argParser = $this->argumentParserFactory->create('__ herp=derp rofl="copter" m');
        list($name, $args, $hash) = $argParser->tokenise();

        $this->assertEquals('__', $name);
        $this->assertCount(1, $args);
        $this->assertEquals('$data->find(\'m\')', $args[0]);
        $this->assertCount(2, $hash);
        $this->assertEquals('$data->find(\'derp\')', $hash['herp']);
        $this->assertEquals("'copter'", $hash['rofl']);
    }

    public function testTokenise7()
    {
        $argParser = $this->argumentParserFactory->create('hbs he"rp=de\'rp be\'ep=true null');
        list($name, $args, $hash) = $argParser->tokenise();

        $this->assertEquals('hbs', $name);
        $this->assertCount(1, $args);
        $this->assertEquals('null', $args[0]);
        $this->assertCount(2, $hash);
        $this->assertEquals('$data->find(\'de\\\'rp\')', $hash['he"rp']);
        $this->assertEquals('true', $hash['be\'ep']);
    }
}
