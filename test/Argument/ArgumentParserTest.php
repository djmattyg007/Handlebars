<?php
declare(strict_types=1);

namespace MattyG\Handlebars\Test\Argument;

use MattyG\Handlebars\Argument;
use PHPUnit\Framework\TestCase;

class ArgumentParserTest extends TestCase
{
    /**
     * @var Argument\ArgumentParserFactory
     */
    protected $argumentParserFactory;

    protected function setUp()
    {
        $this->argumentParserFactory = new Argument\ArgumentParserFactory(new Argument\ArgumentListFactory());
    }

    protected function tearDown()
    {
        $this->argumentParserFactory = null;
    }

    /**
     * @param Argument\Argument $arg
     * @param string $type
     * @param string $value
     * @param string $rawValue
     */
    protected function assertArgumentValues(Argument\Argument $arg, string $type, string $value, string $rawValue)
    {
        $this->assertInstanceOf($type, $arg);
        $this->assertSame($value, $arg->getValue());
        $this->assertSame($rawValue, $arg->getRawValue());
    }

    public function testTokenise1()
    {
        $argParser = $this->argumentParserFactory->create("foobar 'merchant' query.profile_type");
        $argumentList = $argParser->tokenise();

        $this->assertSame('foobar', $argumentList->getName());

        $args = $argumentList->getArguments();
        $this->assertCount(2, $args);
        $this->assertArgumentValues($args[0], Argument\StringArgument::class, "'merchant'", 'merchant');
        $this->assertArgumentValues($args[1], Argument\VariableArgument::class, '$data->find(\'query.profile_type\')', 'query.profile_type');

        $hash = $argumentList->getNamedArguments();
        $this->assertCount(0, $hash);
    }

    public function testTokenise2()
    {
        $argParser = $this->argumentParserFactory->create('foobarbaz 4bar4 4.5 \'some"thi " ng\' 4 "some\'thi \' ng" dog=false cat="meow" mouse=\'squeak squeak\'');
        $argumentList = $argParser->tokenise();

        $this->assertSame('foobarbaz', $argumentList->getName());

        $args = $argumentList->getArguments();
        $this->assertCount(5, $args);
        $this->assertArgumentValues($args[0], Argument\VariableArgument::class, '$data->find(\'4bar4\')', '4bar4');
        $this->assertArgumentValues($args[1], Argument\Argument::class, '4.5', '4.5');
        $this->assertArgumentValues($args[2], Argument\StringArgument::class, "'some\"thi \" ng'", "some\"thi \" ng");
        $this->assertArgumentValues($args[3], Argument\Argument::class, '4', '4');
        $this->assertArgumentValues($args[4], Argument\StringArgument::class, "'some\\'thi \\' ng'", "some'thi ' ng");

        $hash = $argumentList->getNamedArguments();
        $this->assertCount(3, $hash);
        $this->assertArgumentValues($hash["dog"], Argument\Argument::class, "false", "false");
        $this->assertArgumentValues($hash["cat"], Argument\StringArgument::class, "'meow'", "meow");
        $this->assertArgumentValues($hash["mouse"], Argument\StringArgument::class, "'squeak squeak'", "squeak squeak");
    }

    public function testTokenise3()
    {
        $argParser = $this->argumentParserFactory->create('_ \'TODAY\\\'S\' BEST DEALS\'');
        $argumentList = $argParser->tokenise();

        $this->assertSame('_', $argumentList->getName());

        $args = $argumentList->getArguments();
        $this->assertCount(3, $args);
        $this->assertArgumentValues($args[0], Argument\StringArgument::class, "'TODAY\\'S'", "TODAY'S");
        $this->assertArgumentValues($args[1], Argument\VariableArgument::class, '$data->find(\'BEST\')', "BEST");
        $this->assertArgumentValues($args[2], Argument\VariableArgument::class, '$data->find(\'DEALS\\\'\')', "DEALS'");

        $hash = $argumentList->getNamedArguments();
        $this->assertCount(0, $hash);
    }

    public function testTokenise4()
    {
        $argParser = $this->argumentParserFactory->create('abc x ');
        $argumentList = $argParser->tokenise();

        $this->assertSame('abc', $argumentList->getName());

        $args = $argumentList->getArguments();
        $this->assertCount(1, $args);
        $this->assertArgumentValues($args[0], Argument\VariableArgument::class, '$data->find(\'x\')', "x");

        $hash = $argumentList->getNamedArguments();
        $this->assertCount(0, $hash);
    }

    public function testTokenise5()
    {
        $argParser = $this->argumentParserFactory->create('___   a  "b"    cd   hash=hashed');
        $argumentList = $argParser->tokenise();

        $this->assertSame('___', $argumentList->getName());

        $args = $argumentList->getArguments();
        $this->assertCount(3, $args);
        $this->assertArgumentValues($args[0], Argument\VariableArgument::class, '$data->find(\'a\')', "a");
        $this->assertArgumentValues($args[1], Argument\StringArgument::class, "'b'", "b");
        $this->assertArgumentValues($args[2], Argument\VariableArgument::class, '$data->find(\'cd\')', "cd");

        $hash = $argumentList->getNamedArguments();
        $this->assertCount(1, $hash);
        $this->assertArgumentValues($hash["hash"], Argument\VariableArgument::class, '$data->find(\'hashed\')', "hashed");
    }

    public function testTokenise6()
    {
        $argParser = $this->argumentParserFactory->create('__ herp=derp test=4 rofl="copter" m');
        $argumentList = $argParser->tokenise();

        $this->assertSame('__', $argumentList->getName());

        $args = $argumentList->getArguments();
        $this->assertCount(1, $args);
        $this->assertArgumentValues($args[0], Argument\VariableArgument::class, '$data->find(\'m\')', "m");

        $hash = $argumentList->getNamedArguments();
        $this->assertCount(3, $hash);
        $this->assertArgumentValues($hash["herp"], Argument\VariableArgument::class, '$data->find(\'derp\')', "derp");
        $this->assertArgumentValues($hash["test"], Argument\Argument::class, "4", "4");
        $this->assertArgumentValues($hash["rofl"], Argument\StringArgument::class, "'copter'", "copter");
    }

    public function testTokenise7()
    {
        $argParser = $this->argumentParserFactory->create('hbs he"rp=de\'rp be\'ep=true null');
        $argumentList = $argParser->tokenise();

        $this->assertSame('hbs', $argumentList->getName());

        $args = $argumentList->getArguments();
        $this->assertCount(1, $args);
        $this->assertArgumentValues($args[0], Argument\Argument::class, "null", "null");

        $hash = $argumentList->getNamedArguments();
        $this->assertCount(2, $hash);
        $this->assertArgumentValues($hash['he"rp'], Argument\VariableArgument::class, '$data->find(\'de\\\'rp\')', "de'rp");
        $this->assertArgumentValues($hash["be'ep"], Argument\Argument::class, "true", "true");
    }

    public function testTokenise8()
    {
        $argParser = $this->argumentParserFactory->create('abcd "aaa" ( mnop  "bbb"  4 test1  ) test2');
        $argumentList = $argParser->tokenise();

        $this->assertSame('abcd', $argumentList->getName());

        $args = $argumentList->getArguments();
        $this->assertCount(3, $args);
        $this->assertArgumentValues($args[0], Argument\StringArgument::class, "'aaa'", "aaa");
        $this->assertArgumentValues($args[1], Argument\HelperArgument::class, ' mnop  "bbb"  4 test1  ', ' mnop  "bbb"  4 test1  ');
        $this->assertArgumentValues($args[2], Argument\VariableArgument::class, '$data->find(\'test2\')', "test2");

        $helper1ArgList = $args[1]->getArgumentList();
        $this->assertSame('mnop', $helper1ArgList->getName());
        $helper1Args = $helper1ArgList->getArguments();
        $this->assertCount(3, $helper1Args);
        $this->assertArgumentValues($helper1Args[0], Argument\StringArgument::class, "'bbb'", "bbb");
        $this->assertArgumentValues($helper1Args[1], Argument\Argument::class, "4", "4");
        $this->assertArgumentValues($helper1Args[2], Argument\VariableArgument::class, '$data->find(\'test1\')', "test1");
        $helper1Hash = $helper1ArgList->getNamedArguments();
        $this->assertCount(0, $helper1Hash);

        $hash = $argumentList->getNamedArguments();
        $this->assertCount(0, $hash);
    }

    public function testTokenise9()
    {
        $argParser = $this->argumentParserFactory->create('xyz (pqrst ghikl beep=\'honk\') "hello world " 9.0');
        $argumentList = $argParser->tokenise();

        $this->assertSame('xyz', $argumentList->getName());

        $args = $argumentList->getArguments();
        $this->assertCount(3, $args);
        $this->assertArgumentValues($args[0], Argument\HelperArgument::class, "pqrst ghikl beep='honk'", "pqrst ghikl beep='honk'");
        $this->assertArgumentValues($args[1], Argument\StringArgument::class, "'hello world '", "hello world ");
        $this->assertArgumentValues($args[2], Argument\Argument::class, "9.0", "9.0");

        $helper1ArgList = $args[0]->getArgumentList();
        $this->assertSame('pqrst', $helper1ArgList->getName());
        $helper1Args = $helper1ArgList->getArguments();
        $this->assertCount(1, $helper1Args);
        $this->assertArgumentValues($helper1Args[0], Argument\VariableArgument::class, '$data->find(\'ghikl\')', "ghikl");
        $helper1Hash = $helper1ArgList->getNamedArguments();
        $this->assertCount(1, $helper1Hash);
        $this->assertArgumentValues($helper1Hash["beep"], Argument\StringArgument::class, "'honk'", "honk");

        $hash = $argumentList->getNamedArguments();
        $this->assertCount(0, $hash);
    }

    public function testTokenise10()
    {
        $argParser = $this->argumentParserFactory->create('abc (a1 b1=b2  (c3 "gh\'i" lmnop) 4) (yeah "ha" ha)');
        $argumentList = $argParser->tokenise();

        $this->assertSame('abc', $argumentList->getName());

        $args = $argumentList->getArguments();
        $this->assertCount(2, $args);
        $this->assertArgumentValues($args[0], Argument\HelperArgument::class, 'a1 b1=b2  (c3 "gh\'i" lmnop) 4', 'a1 b1=b2  (c3 "gh\'i" lmnop) 4');
        $this->assertArgumentValues($args[1], Argument\HelperArgument::class, 'yeah "ha" ha', 'yeah "ha" ha');

        $helper1ArgList = $args[0]->getArgumentList();
        $this->assertSame('a1', $helper1ArgList->getName());
        $helper1Args = $helper1ArgList->getArguments();
        $this->assertCount(2, $helper1Args);
        $this->assertArgumentValues($helper1Args[0], Argument\HelperArgument::class, 'c3 "gh\'i" lmnop', 'c3 "gh\'i" lmnop');
        $this->assertArgumentValues($helper1Args[1], Argument\Argument::class, "4", "4");
        $helper1Hash = $helper1ArgList->getNamedArguments();
        $this->assertCount(1, $helper1Hash);
        $this->assertArgumentValues($helper1Hash["b1"], Argument\VariableArgument::class, '$data->find(\'b2\')', "b2");

        $helper2ArgList = $helper1Args[0]->getArgumentList();
        $this->assertSame('c3', $helper2ArgList->getName());
        $helper2Args = $helper2ArgList->getArguments();
        $this->assertCount(2, $helper2Args);
        $this->assertArgumentValues($helper2Args[0], Argument\StringArgument::class, "'gh\\'i'", "gh'i");
        $this->assertArgumentValues($helper2Args[1], Argument\VariableArgument::class, '$data->find(\'lmnop\')', "lmnop");
        $helper2Hash = $helper2ArgList->getNamedArguments();
        $this->assertCount(0, $helper2Hash);

        $hash = $argumentList->getNamedArguments();
        $this->assertCount(0, $hash);
    }

    public function testTokenise11()
    {
        $argParser = $this->argumentParserFactory->create('aeiou test1=(test2 1 "xyz") "abc"');
        $argumentList = $argParser->tokenise();

        $this->assertSame('aeiou', $argumentList->getName());

        $args = $argumentList->getArguments();
        $this->assertCount(1, $args);
        $this->assertArgumentValues($args[0], Argument\StringArgument::class, "'abc'", "abc");

        $hash = $argumentList->getNamedArguments();
        $this->assertCount(1, $hash);
        $this->assertArgumentValues($hash["test1"], Argument\HelperArgument::class, 'test2 1 "xyz"', 'test2 1 "xyz"');

        $helper1ArgList = $hash['test1']->getArgumentList();
        $this->assertSame('test2', $helper1ArgList->getName());
        $helper1Args = $helper1ArgList->getArguments();
        $this->assertCount(2, $helper1Args);
        $this->assertArgumentValues($helper1Args[0], Argument\Argument::class, "1", "1");
        $this->assertArgumentValues($helper1Args[1], Argument\StringArgument::class, "'xyz'", "xyz");
        $helper1Hash = $helper1ArgList->getNamedArguments();
        $this->assertCount(0, $helper1Hash);
    }

    public function testTokenise12()
    {
        $argParser = $this->argumentParserFactory->create("matthew");
        $argumentList = $argParser->tokenise();

        $this->assertSame("matthew", $argumentList->getName());

        $args = $argumentList->getArguments();
        $this->assertCount(0, $args);
        $hash = $argumentList->getNamedArguments();
        $this->assertCount(0, $hash);
    }

    public function testTokenise13()
    {
        $argParser = $this->argumentParserFactory->create("matrix (neo)");
        $argumentList = $argParser->tokenise();

        $this->assertSame("matrix", $argumentList->getName());

        $args = $argumentList->getArguments();
        $this->assertCount(1, $args);
        $this->assertArgumentValues($args[0], Argument\HelperArgument::class, "neo", "neo");

        $helper1ArgList = $args[0]->getArgumentList();
        $this->assertSame("neo", $helper1ArgList->getName());
        $helper1Args = $helper1ArgList->getArguments();
        $this->assertCount(0, $helper1Args);
        $helper1Hash = $helper1ArgList->getNamedArguments();
        $this->assertCount(0, $helper1Hash);

        $hash = $argumentList->getNamedArguments();
        $this->assertCount(0, $hash);
    }

    public function testTokenise14()
    {
        $argParser = $this->argumentParserFactory->create("potter spells=(book)");
        $argumentList = $argParser->tokenise();

        $this->assertSame("potter", $argumentList->getName());

        $args = $argumentList->getArguments();
        $this->assertCount(0, $args);

        $hash = $argumentList->getNamedArguments();
        $this->assertCount(1, $hash);
        $this->assertArgumentValues($hash["spells"], Argument\HelperArgument::class, "book", "book");

        $helper1ArgList = $hash["spells"]->getArgumentList();
        $this->assertSame("book", $helper1ArgList->getName());
        $helper1Args = $helper1ArgList->getArguments();
        $this->assertCount(0, $helper1Args);
        $helper1Hash = $helper1ArgList->getNamedArguments();
        $this->assertCount(0, $helper1Hash);
    }

    public function testTokenise15()
    {
        $argParser = $this->argumentParserFactory->create("artemis '(test fakehelper)'");
        $argumentList = $argParser->tokenise();

        $this->assertSame("artemis", $argumentList->getName());

        $args = $argumentList->getArguments();
        $this->assertCount(1, $args);
        $this->assertArgumentValues($args[0], Argument\StringArgument::class, "'(test fakehelper)'", "(test fakehelper)");

        $hash = $argumentList->getNamedArguments();
        $this->assertCount(0, $hash);
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
