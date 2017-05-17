<?php
declare(strict_types=1);

namespace MattyG\Handlebars\Test\Argument;

use MattyG\Handlebars;
use PHPUnit\Framework\TestCase;

class ArgumentParser extends TestCase
{
    /**
     * @var Handlebars\Argument\ArgumentParserFactory
     */
    protected $argumentParserFactory;

    protected function setUp()
    {
        $this->argumentParserFactory = new Handlebars\Argument\ArgumentParserFactory(new Handlebars\Argument\ArgumentListFactory());
    }

    protected function tearDown()
    {
        $this->argumentParserFactory = null;
    }

    public function testTokenise1()
    {
        $argParser = $this->argumentParserFactory->create("foobar 'merchant' query.profile_type");
        $argumentList = $argParser->tokenise();

        $this->assertEquals('foobar', $argumentList->getName());

        $args = $argumentList->getArguments();
        $this->assertCount(2, $args);
        $this->assertInstanceOf(Handlebars\Argument\StringArgument::class, $args[0]);
        $this->assertEquals("'merchant'", $args[0]->getValue());
        $this->assertEquals('merchant', $args[0]->getRawValue());
        $this->assertInstanceOf(Handlebars\Argument\VariableArgument::class, $args[1]);
        $this->assertEquals('$data->find(\'query.profile_type\')', $args[1]->getValue());
        $this->assertEquals('query.profile_type', $args[1]->getRawValue());

        $hash = $argumentList->getNamedArguments();
        $this->assertCount(0, $hash);
    }

    public function testTokenise2()
    {
        $argParser = $this->argumentParserFactory->create('foobarbaz 4bar4 4.5 \'some"thi " ng\' 4 "some\'thi \' ng" dog=false cat="meow" mouse=\'squeak squeak\'');
        $argumentList = $argParser->tokenise();

        $this->assertEquals('foobarbaz', $argumentList->getName());

        $args = $argumentList->getArguments();
        $this->assertCount(5, $args);
        $this->assertInstanceOf(Handlebars\Argument\VariableArgument::class, $args[0]);
        $this->assertEquals('$data->find(\'4bar4\')', $args[0]->getValue());
        $this->assertEquals('4bar4', $args[0]->getRawValue());
        $this->assertInstanceOf(Handlebars\Argument\Argument::class, $args[1]);
        $this->assertEquals('4.5', $args[1]->getValue());
        $this->assertEquals('4.5', $args[1]->getRawValue());
        $this->assertInstanceOf(Handlebars\Argument\StringArgument::class, $args[2]);
        $this->assertEquals("'some\"thi \" ng'", $args[2]->getValue());
        $this->assertEquals("some\"thi \" ng", $args[2]->getRawValue());
        $this->assertInstanceOf(Handlebars\Argument\Argument::class, $args[3]);
        $this->assertEquals('4', $args[3]->getValue());
        $this->assertEquals('4', $args[3]->getRawValue());
        $this->assertInstanceOf(Handlebars\Argument\StringArgument::class, $args[4]);
        $this->assertEquals("'some\\'thi \\' ng'", $args[4]->getValue());
        $this->assertEquals("some'thi ' ng", $args[4]->getRawValue());

        $hash = $argumentList->getNamedArguments();
        $this->assertCount(3, $hash);
        $this->assertInstanceOf(Handlebars\Argument\Argument::class, $hash['dog']);
        $this->assertEquals('false', $hash['dog']->getValue());
        $this->assertEquals('false', $hash['dog']->getRawValue());
        $this->assertInstanceOf(Handlebars\Argument\StringArgument::class, $hash['cat']);
        $this->assertEquals("'meow'", $hash['cat']->getValue());
        $this->assertEquals("meow", $hash['cat']->getRawValue());
        $this->assertInstanceOf(Handlebars\Argument\StringArgument::class, $hash['mouse']);
        $this->assertEquals("'squeak squeak'", $hash['mouse']->getValue());
        $this->assertEquals("squeak squeak", $hash['mouse']->getRawValue());
    }

    public function testTokenise3()
    {
        $argParser = $this->argumentParserFactory->create('_ \'TODAY\\\'S\' BEST DEALS\'');
        $argumentList = $argParser->tokenise();

        $this->assertEquals('_', $argumentList->getName());

        $args = $argumentList->getArguments();
        $this->assertCount(3, $args);
        $this->assertInstanceOf(Handlebars\Argument\StringArgument::class, $args[0]);
        $this->assertEquals("'TODAY\\'S'", $args[0]->getValue());
        $this->assertEquals("TODAY'S", $args[0]->getRawValue());
        $this->assertInstanceOf(Handlebars\Argument\VariableArgument::class, $args[1]);
        $this->assertEquals('$data->find(\'BEST\')', $args[1]->getValue());
        $this->assertEquals("BEST", $args[1]->getRawValue());
        $this->assertInstanceOf(Handlebars\Argument\VariableArgument::class, $args[2]);
        $this->assertEquals('$data->find(\'DEALS\\\'\')', $args[2]->getValue());
        $this->assertEquals("DEALS'", $args[2]->getRawValue());

        $hash = $argumentList->getNamedArguments();
        $this->assertCount(0, $hash);
    }

    public function testTokenise4()
    {
        $argParser = $this->argumentParserFactory->create('abc x ');
        $argumentList = $argParser->tokenise();

        $this->assertEquals('abc', $argumentList->getName());

        $args = $argumentList->getArguments();
        $this->assertCount(1, $args);
        $this->assertEquals('$data->find(\'x\')', $args[0]->getValue());
        $this->assertEquals("x", $args[0]->getRawValue());

        $hash = $argumentList->getNamedArguments();
        $this->assertCount(0, $hash);
    }

    public function testTokenise5()
    {
        $argParser = $this->argumentParserFactory->create('___   a  "b"    cd   hash=hashed');
        $argumentList = $argParser->tokenise();

        $this->assertEquals('___', $argumentList->getName());

        $args = $argumentList->getArguments();
        $this->assertCount(3, $args);
        $this->assertInstanceOf(Handlebars\Argument\VariableArgument::class, $args[0]);
        $this->assertEquals('$data->find(\'a\')', $args[0]->getValue());
        $this->assertEquals("a", $args[0]->getRawValue());
        $this->assertInstanceOf(Handlebars\Argument\StringArgument::class, $args[1]);
        $this->assertEquals("'b'", $args[1]->getValue());
        $this->assertEquals("b", $args[1]->getRawValue());
        $this->assertInstanceOf(Handlebars\Argument\VariableArgument::class, $args[2]);
        $this->assertEquals('$data->find(\'cd\')', $args[2]->getValue());
        $this->assertEquals("cd", $args[2]->getRawValue());

        $hash = $argumentList->getNamedArguments();
        $this->assertCount(1, $hash);
        $this->assertInstanceOf(Handlebars\Argument\VariableArgument::class, $hash['hash']);
        $this->assertEquals('$data->find(\'hashed\')', $hash['hash']->getValue());
        $this->assertEquals("hashed", $hash['hash']->getRawValue());
    }

    public function testTokenise6()
    {
        $argParser = $this->argumentParserFactory->create('__ herp=derp test=4 rofl="copter" m');
        $argumentList = $argParser->tokenise();

        $this->assertEquals('__', $argumentList->getName());

        $args = $argumentList->getArguments();
        $this->assertCount(1, $args);
        $this->assertInstanceOf(Handlebars\Argument\VariableArgument::class, $args[0]);
        $this->assertEquals('$data->find(\'m\')', $args[0]->getValue());
        $this->assertEquals("m", $args[0]->getRawValue());

        $hash = $argumentList->getNamedArguments();
        $this->assertCount(3, $hash);
        $this->assertInstanceOf(Handlebars\Argument\VariableArgument::class, $hash['herp']);
        $this->assertEquals('$data->find(\'derp\')', $hash['herp']->getValue());
        $this->assertEquals("derp", $hash['herp']->getRawValue());
        $this->assertInstanceOf(Handlebars\Argument\Argument::class, $hash['test']);
        $this->assertEquals("4", $hash['test']->getValue());
        $this->assertEquals("4", $hash['test']->getRawValue());
        $this->assertInstanceOf(Handlebars\Argument\StringArgument::class, $hash['rofl']);
        $this->assertEquals("'copter'", $hash['rofl']->getValue());
        $this->assertEquals("copter", $hash['rofl']->getRawValue());
    }

    public function testTokenise7()
    {
        $argParser = $this->argumentParserFactory->create('hbs he"rp=de\'rp be\'ep=true null');
        $argumentList = $argParser->tokenise();

        $this->assertEquals('hbs', $argumentList->getName());

        $args = $argumentList->getArguments();
        $this->assertCount(1, $args);
        $this->assertInstanceOf(Handlebars\Argument\Argument::class, $args[0]);
        $this->assertEquals('null', $args[0]->getValue());
        $this->assertEquals('null', $args[0]->getRawValue());

        $hash = $argumentList->getNamedArguments();
        $this->assertCount(2, $hash);
        $this->assertInstanceOf(Handlebars\Argument\VariableArgument::class, $hash['he"rp']);
        $this->assertEquals('$data->find(\'de\\\'rp\')', $hash['he"rp']->getValue());
        $this->assertEquals("de'rp", $hash['he"rp']->getRawValue());
        $this->assertInstanceOf(Handlebars\Argument\Argument::class, $hash["be'ep"]);
        $this->assertEquals('true', $hash["be'ep"]->getValue());
        $this->assertEquals('true', $hash["be'ep"]->getRawValue());
    }

    public function testTokenise8()
    {
        $argParser = $this->argumentParserFactory->create('abcd "aaa" ( mnop  "bbb"  4 test1  ) test2');
        $argumentList = $argParser->tokenise();

        $this->assertEquals('abcd', $argumentList->getName());

        $args = $argumentList->getArguments();
        $this->assertCount(3, $args);
        $this->assertInstanceOf(Handlebars\Argument\StringArgument::class, $args[0]);
        $this->assertEquals("'aaa'", $args[0]->getValue());
        $this->assertEquals('aaa', $args[0]->getRawValue());
        $this->assertInstanceOf(Handlebars\Argument\HelperArgument::class, $args[1]);
        $this->assertEquals(' mnop  "bbb"  4 test1  ', $args[1]->getValue());
        $this->assertEquals(' mnop  "bbb"  4 test1  ', $args[1]->getRawValue());
        $this->assertInstanceOf(Handlebars\Argument\VariableArgument::class, $args[2]);
        $this->assertEquals('$data->find(\'test2\')', $args[2]->getValue());
        $this->assertEquals('test2', $args[2]->getRawValue());

        $helper1ArgList = $args[1]->getArgumentList();
        $this->assertEquals('mnop', $helper1ArgList->getName());
        $helper1Args = $helper1ArgList->getArguments();
        $this->assertCount(3, $helper1Args);
        $this->assertInstanceOf(Handlebars\Argument\StringArgument::class, $helper1Args[0]);
        $this->assertEquals("'bbb'", $helper1Args[0]->getValue());
        $this->assertEquals('bbb', $helper1Args[0]->getRawValue());
        $this->assertInstanceOf(Handlebars\Argument\Argument::class, $helper1Args[1]);
        $this->assertEquals('4', $helper1Args[1]->getValue());
        $this->assertEquals('4', $helper1Args[1]->getRawValue());
        $this->assertInstanceOf(Handlebars\Argument\VariableArgument::class, $helper1Args[2]);
        $this->assertEquals('$data->find(\'test1\')', $helper1Args[2]->getValue());
        $this->assertEquals('test1', $helper1Args[2]->getRawValue());
        $helper1Hash = $helper1ArgList->getNamedArguments();
        $this->assertCount(0, $helper1Hash);

        $hash = $argumentList->getNamedArguments();
        $this->assertCount(0, $hash);
    }

    public function testTokenise9()
    {
        $argParser = $this->argumentParserFactory->create('xyz (pqrst ghikl beep=\'honk\') "hello world " 9.0');
        $argumentList = $argParser->tokenise();

        $this->assertEquals('xyz', $argumentList->getName());

        $args = $argumentList->getArguments();
        $this->assertCount(3, $args);
        $this->assertInstanceOf(Handlebars\Argument\HelperArgument::class, $args[0]);
        $this->assertEquals("pqrst ghikl beep='honk'", $args[0]->getValue());
        $this->assertEquals("pqrst ghikl beep='honk'", $args[0]->getRawValue());
        $this->assertInstanceOf(Handlebars\Argument\StringArgument::class, $args[1]);
        $this->assertEquals("'hello world '", $args[1]->getValue());
        $this->assertEquals('hello world ', $args[1]->getRawValue());
        $this->assertInstanceOf(Handlebars\Argument\Argument::class, $args[2]);
        $this->assertEquals('9.0', $args[2]->getValue());
        $this->assertEquals('9.0', $args[2]->getRawValue());

        $helper1ArgList = $args[0]->getArgumentList();
        $this->assertEquals('pqrst', $helper1ArgList->getName());
        $helper1Args = $helper1ArgList->getArguments();
        $this->assertCount(1, $helper1Args);
        $this->assertInstanceOf(Handlebars\Argument\VariableArgument::class, $helper1Args[0]);
        $this->assertEquals('$data->find(\'ghikl\')', $helper1Args[0]->getValue());
        $this->assertEquals('ghikl', $helper1Args[0]->getRawValue());
        $helper1Hash = $helper1ArgList->getNamedArguments();
        $this->assertCount(1, $helper1Hash);
        $this->assertInstanceOf(Handlebars\Argument\StringArgument::class, $helper1Hash['beep']);
        $this->assertEquals("'honk'", $helper1Hash['beep']->getValue());
        $this->assertEquals('honk', $helper1Hash['beep']->getRawValue());

        $hash = $argumentList->getNamedArguments();
        $this->assertCount(0, $hash);
    }

    public function testTokenise10()
    {
        $argParser = $this->argumentParserFactory->create('abc (a1 b1=b2  (c3 "gh\'i" lmnop) 4) (yeah "ha" ha)');
        $argumentList = $argParser->tokenise();

        $this->assertEquals('abc', $argumentList->getName());

        $args = $argumentList->getArguments();
        $this->assertCount(2, $args);
        $this->assertInstanceOf(Handlebars\Argument\HelperArgument::class, $args[0]);
        $this->assertEquals('a1 b1=b2  (c3 "gh\'i" lmnop) 4', $args[0]->getValue());
        $this->assertEquals('a1 b1=b2  (c3 "gh\'i" lmnop) 4', $args[0]->getRawValue());
        $this->assertInstanceOf(Handlebars\Argument\HelperArgument::class, $args[1]);
        $this->assertEquals('yeah "ha" ha', $args[1]->getValue());
        $this->assertEquals('yeah "ha" ha', $args[1]->getRawValue());

        $helper1ArgList = $args[0]->getArgumentList();
        $this->assertEquals('a1', $helper1ArgList->getName());
        $helper1Args = $helper1ArgList->getArguments();
        $this->assertCount(2, $helper1Args);
        $this->assertInstanceOf(Handlebars\Argument\HelperArgument::class, $helper1Args[0]);
        $this->assertEquals('c3 "gh\'i" lmnop', $helper1Args[0]->getValue());
        $this->assertEquals('c3 "gh\'i" lmnop', $helper1Args[0]->getRawValue());
        $this->assertInstanceOf(Handlebars\Argument\Argument::class, $helper1Args[1]);
        $this->assertEquals('4', $helper1Args[1]->getValue());
        $this->assertEquals('4', $helper1Args[1]->getRawValue());
        $helper1Hash = $helper1ArgList->getNamedArguments();
        $this->assertCount(1, $helper1Hash);
        $this->assertInstanceOf(Handlebars\Argument\VariableArgument::class, $helper1Hash['b1']);
        $this->assertEquals('$data->find(\'b2\')', $helper1Hash['b1']->getValue());
        $this->assertEquals('b2', $helper1Hash['b1']->getRawValue());

        $helper2ArgList = $helper1Args[0]->getArgumentList();
        $this->assertEquals('c3', $helper2ArgList->getName());
        $helper2Args = $helper2ArgList->getArguments();
        $this->assertCount(2, $helper2Args);
        $this->assertInstanceOf(Handlebars\Argument\StringArgument::class, $helper2Args[0]);
        $this->assertEquals("'gh\\'i'", $helper2Args[0]->getValue());
        $this->assertEquals('gh\'i', $helper2Args[0]->getRawValue());
        $this->assertInstanceOf(Handlebars\Argument\VariableArgument::class, $helper2Args[1]);
        $this->assertEquals('$data->find(\'lmnop\')', $helper2Args[1]->getValue());
        $this->assertEquals('lmnop', $helper2Args[1]->getRawValue());
        $helper2Hash = $helper2ArgList->getNamedArguments();
        $this->assertCount(0, $helper2Hash);

        $hash = $argumentList->getNamedArguments();
        $this->assertCount(0, $hash);
    }

    public function testTokenise11()
    {
        $argParser = $this->argumentParserFactory->create('aeiou test1=(test2 1 "xyz") "abc"');
        $argumentList = $argParser->tokenise();

        $this->assertEquals('aeiou', $argumentList->getName());

        $args = $argumentList->getArguments();
        $this->assertCount(1, $args);
        $this->assertInstanceOf(Handlebars\Argument\StringArgument::class, $args[0]);
        $this->assertEquals("'abc'", $args[0]->getValue());
        $this->assertEquals('abc', $args[0]->getRawValue());

        $hash = $argumentList->getNamedArguments();
        $this->assertCount(1, $hash);
        $this->assertInstanceOf(Handlebars\Argument\HelperArgument::class, $hash['test1']);
        $this->assertEquals('test2 1 "xyz"', $hash['test1']->getValue());
        $this->assertEquals('test2 1 "xyz"', $hash['test1']->getRawValue());

        $helper1ArgList = $hash['test1']->getArgumentList();
        $this->assertEquals('test2', $helper1ArgList->getName());
        $helper1Args = $helper1ArgList->getArguments();
        $this->assertCount(2, $helper1Args);
        $this->assertInstanceOf(Handlebars\Argument\Argument::class, $helper1Args[0]);
        $this->assertEquals('1', $helper1Args[0]->getValue());
        $this->assertEquals('1', $helper1Args[0]->getRawValue());
        $this->assertInstanceOf(Handlebars\Argument\StringArgument::class, $helper1Args[1]);
        $this->assertEquals("'xyz'", $helper1Args[1]->getValue());
        $this->assertEquals('xyz', $helper1Args[1]->getRawValue());
        $helper1Hash = $helper1ArgList->getNamedArguments();
        $this->assertCount(0, $helper1Hash);
    }

    /**
     * @expectedException MattyG\Handlebars\Argument\Exception
     * @expectedExceptionMessage Non-whitespace character detected after string argument:
     */
    public function testTokeniseException1()
    {
        $argParser = $this->argumentParserFactory->create('abcdef "rofl"copter a1');
        $argParser->tokenise();
    }
}
