<?php //-->
/**
 * This file is part of the Eden PHP Library.
 * (c) 2014-2016 Openovate Labs
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */
class Eden_Handlebars_Tokenizer_Test extends PHPUnit_Framework_TestCase
{
	public function testTokenize()
	{
		//load the source
		$source = file_get_contents(__DIR__.'/assets/tokenizer.html');
		
		$i = 0;
		
		//should we test for more?
		$tests = array(
			'<div class="product-fields">
	<div class="form-group',
		"if errors.product_title",
		' has-error',
		"if",
		' clearfix">
        <label class="control-label">',
			"_ 'Title'",
			'</label>
        <div>
            <input
                type="text"
                class="form-control"
                name="product_title"
                placeholder="',
			"_ 'What is the name of this product?'",
			'"
                value="',
			"item.product_title",
			'" />

            ',
			"if errors.product_title",
			'
            <span class="help-text text-danger">',
			"errors.product_title",
			'</span>
            ',
			"if",
			'
        </div>
    </div>

    <div class="form-group'
		);
		
		$unit = $this;
		
		Eden\Handlebars\Tokenizer::i($source)->tokenize(function($node) use ($unit, $tests, &$i) {
			if(isset($tests[$i])) {
				$unit->assertEquals($tests[$i], $node['value']);
			}

			$i++;
		});
		
	}
}