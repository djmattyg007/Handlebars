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

class Tokenizer extends \PHPUnit_Framework_TestCase
{
    public function testTokenize()
    {
        //load the source
        $source = file_get_contents(__DIR__ . '/assets/tokenizer.html');
        $tokenizer = new Handlebars\Tokenizer($source);

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

    <div class="form-group',
            'if errors.product_detail',
        );

        $tokenizer->tokenize(function($node) use ($tests, &$i) {
            // Currently we don't test all of the tokens in tokenizer.html, but there's no easy way to stop
            // the tokenizer half-way. This achieves that effect in a somewhat haphazard manner.
            if (isset($tests[$i])) {
                $this->assertEquals($tests[$i], $node['value']);
            }

            $i++;
        });
    }
}
