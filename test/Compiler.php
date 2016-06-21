<?php
/**
 * This file is part of the Eden PHP Library.
 * (c) 2014-2016 Openovate Labs
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */
 
/**
 * The following tests were pulled from the Mustache.php Library
 * and kept as is with changes to the final class name to test 
 * backwards compatibility
 */
class Eden_Handlebars_Compiler_Test extends PHPUnit_Framework_TestCase
{
    public function testGetSource()
    {    
        //load the source
        $source = file_get_contents(__DIR__.'/assets/tokenizer.html');

        $index = IndexStub::i();

        $template = Eden\Handlebars\Compiler::i($index, $source)->getSource();
        
        $this->assertEquals($source, $template);
    }

    public function testCompile()
    {
        //load the source
        $source = file_get_contents(__DIR__.'/assets/tokenizer.html');
        $template1 = file_get_contents(__DIR__.'/assets/template1.php');
        $template2 = file_get_contents(__DIR__.'/assets/template2.php');

        $index = IndexStub::i();

        $code = Eden\Handlebars\Compiler::i($index, $source)->compile();
        
        $this->assertEquals($template1, $code);
        
        $code = Eden\Handlebars\Compiler::i($index, $source)->compile(false);
        
        $this->assertEquals($template2, $code);
    }

    public function testSetOffset()
    {
        $source = file_get_contents(__DIR__.'/assets/tokenizer.html');

        $index = IndexStub::i();

        $instance = Eden\Handlebars\Compiler::i($index, $source)->setOffset(3);
        
        $this->assertInstanceOf('Eden\\Handlebars\\Compiler', $instance);
    }
    
    public function testParseArguments()
    {
        $source = file_get_contents(__DIR__.'/assets/tokenizer.html');
        
        $index = IndexStub::i();
        
        $compiler = CompilerStub::i($index, $source);
        
        //basic
        list($name, $args, $hash) = $compiler->parseArgumentsStub("foobar 'merchant' query.profile_type");
        
        $this->assertCount(2, $args);
        
        //advanced
        list($name, $args, $hash) = $compiler->parseArgumentsStub(
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
        list($name, $args, $hash) = $compiler->parseArgumentsStub('_ \'TODAY\'S BEST DEALS\'');
        
        $this->assertCount(4, $args);
        $this->assertEquals('\'TODAY\'', $args[0]);
        $this->assertEquals('$data->find(\'S\')', $args[1]);
        $this->assertEquals('$data->find(\'BEST\')', $args[2]);
        $this->assertEquals('$data->find(\'DEALS\\\'\')', $args[3]);
    }
}

if(!class_exists('IndexStub')) {
    class IndexStub extends Eden\Handlebars\Index
    {
    }
}


if(!class_exists('CompilerStub')) {
    class CompilerStub extends Eden\Handlebars\Compiler
    {
        public function parseArgumentsStub($string)
        {
            return $this->parseArguments($string);
        }
    }
}
