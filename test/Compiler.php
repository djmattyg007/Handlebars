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

/**
 * The following tests were pulled from the Mustache.php Library
 * and kept as is with changes to the final class name to test 
 * backwards compatibility
 */
class Eden_Handlebars_Compiler_Test extends PHPUnit_Framework_TestCase
{
    public function testCompile()
    {
        //load the source
        $source = trim(file_get_contents(__DIR__ . '/assets/tokenizer.html'));
        $tokenizer = new Handlebars\Tokenizer($source);
        $template1 = file_get_contents(__DIR__ . '/assets/template1.php');
        $template2 = file_get_contents(__DIR__ . '/assets/template2.php');

        $index = new Handlebars\Index();

        $code = (new Handlebars\Compiler($index, $tokenizer))->compile();
        $this->assertEquals($template1, $code);

        $code = (new Handlebars\Compiler($index, $tokenizer))->compile(false);
        $this->assertEquals($template2, $code);
    }

    public function testSetOffset()
    {
        $source = file_get_contents(__DIR__ . '/assets/tokenizer.html');
        $tokenizer = new Handlebars\Tokenizer($source);
        $index = new Handlebars\Index();

        $instance = (new Handlebars\Compiler($index, $tokenizer))->setOffset(3);
        $this->assertInstanceOf(Handlebars\Compiler::class, $instance);
    }

    public function testParseArguments()
    {
        $parseArgsMethod = new \ReflectionMethod(Handlebars\Compiler::class, "parseArguments");
        $parseArgsMethod->setAccessible(true);

        $source = file_get_contents(__DIR__ . '/assets/tokenizer.html');
        $tokenizer = new Handlebars\Tokenizer($source);
        $index = new Handlebars\Index();

        $compiler = new Handlebars\Compiler($index, $tokenizer);

        //basic
        list($name, $args, $hash) = $parseArgsMethod->invoke($compiler, "foobar 'merchant' query.profile_type");
        $this->assertCount(2, $args);

        //advanced
        list($name, $args, $hash) = $parseArgsMethod->invoke($compiler,
            'foobar 4bar4 4.5 \'some"thi " ng\' 4 "some\'thi \' ng" '
            .'dog=false cat="meow" mouse=\'squeak squeak\'');

        $this->assertCount(5, $args);
        $this->assertEquals('$data->find(\'4bar4\')', $args[0]);
        $this->assertEquals(4.5, $args[1]);
        $this->assertEquals('\'some"thi " ng\'', $args[2]);
        $this->assertEquals(4, $args[3]);
        $this->assertEquals('\'some\\\'thi \\\' ng\'', $args[4]);
        $this->assertEquals('false', $hash['dog']);
        $this->assertEquals('\'meow\'', $hash['cat']);
        $this->assertEquals('\'squeak squeak\'', $hash['mouse']);

        //BUG: '_ \'TODAY\'S BEST DEALS\''
        list($name, $args, $hash) = $parseArgsMethod->invoke($compiler, '_ \'TODAY\'S BEST DEALS\'');

        $this->assertCount(4, $args);
        $this->assertEquals('\'TODAY\'', $args[0]);
        $this->assertEquals('$data->find(\'S\')', $args[1]);
        $this->assertEquals('$data->find(\'BEST\')', $args[2]);
        $this->assertEquals('$data->find(\'DEALS\\\'\')', $args[3]);
    }
}
