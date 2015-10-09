<?php
/*
 * This file is part of the Utility package of the Eden PHP Library.
 * (c) 2013-2014 Openovate Labs
 *
 * Copyright and license information can be found at LICENSE
 * distributed with this package.
 */
 
/**
 * The following tests were pulled from the Mustache.php Library
 * and kept as is with changes to the final class name to test 
 * backwards compatibility
 */
class EdenHandlebarsTemplateTest extends PHPUnit_Framework_TestCase
{
    public function testConstructor()
    {
        $mustache = new Mustache_Engine();
        $template = new TemplateStub($mustache);
        $this->assertSame($mustache, $template->getMustache());
    }

    public function testRendering()
    {
        $rendered = '<< wheee >>';
        $mustache = new Mustache_Engine();
        $template = new TemplateStub($mustache);
        $template->rendered = $rendered;
        $context  = new Mustache_Context();

        if (version_compare(PHP_VERSION, '5.3.0', '>=')) {
            $this->assertEquals($rendered, $template());
        }

        $this->assertEquals($rendered, $template->render());
        $this->assertEquals($rendered, $template->renderInternal($context));
        $this->assertEquals($rendered, $template->render(array('foo' => 'bar')));
    }
}

class TemplateStub extends Eden\Handlebars\Template
{
    public $rendered;

    public function getMustache()
    {
        return $this->mustache;
    }

    public function renderInternal(Mustache_Context $context, $indent = '', $escape = false)
    {
        return $this->rendered;
    }
}
