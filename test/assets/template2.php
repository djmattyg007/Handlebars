
    $buffer .= '<div class="product-fields">'."\n";
    $buffer .= '	<div class="form-group';
    $buffer .= $helper['if'](
        $data->find('errors.product_title'), 
        array(
            'name' => 'if',
            'args' => 'if errors.product_title',
            'hash' => array(),
            'fn' => function($context = null) use ($noop, $data, &$helper) {
                if(is_array($context)) {
                    $data->push($context);
                }

                $buffer = '';
                $buffer .= ' has-error';

                if(is_array($context)) {
                    $data->pop();
                }

                return $buffer;
            },

            'inverse' => $noop
        )
    );

    $buffer .= ' clearfix">'."\n";
    $buffer .= '        <label class="control-label">';
    $buffer .= htmlspecialchars($data->find('_ \'Title\''), ENT_COMPAT, 'UTF-8');

    $buffer .= '</label>'."\n";
    $buffer .= '        <div>'."\n";
    $buffer .= '            <input'."\n";
    $buffer .= '                type="text"'."\n";
    $buffer .= '                class="form-control"'."\n";
    $buffer .= '                name="product_title"'."\n";
    $buffer .= '                placeholder="';
    $buffer .= htmlspecialchars($data->find('_ \'What is the name of this product?\''), ENT_COMPAT, 'UTF-8');

    $buffer .= '"'."\n";
    $buffer .= '                value="';
    $buffer .= htmlspecialchars($data->find('item.product_title'), ENT_COMPAT, 'UTF-8');

    $buffer .= '" />'."\n";
    $buffer .= "\n";
    $buffer .= '            ';
    $buffer .= $helper['if'](
        $data->find('errors.product_title'), 
        array(
            'name' => 'if',
            'args' => 'if errors.product_title',
            'hash' => array(),
            'fn' => function($context = null) use ($noop, $data, &$helper) {
                if(is_array($context)) {
                    $data->push($context);
                }

                $buffer = '';
                $buffer .= "\n";
                $buffer .= '            <span class="help-text text-danger">';
                $buffer .= htmlspecialchars($data->find('errors.product_title'), ENT_COMPAT, 'UTF-8');

                $buffer .= '</span>'."\n";
                $buffer .= '            ';

                if(is_array($context)) {
                    $data->pop();
                }

                return $buffer;
            },

            'inverse' => $noop
        )
    );

    $buffer .= "\n";
    $buffer .= '        </div>'."\n";
    $buffer .= '    </div>'."\n";
    $buffer .= "\n";
    $buffer .= '    <div class="form-group';
    $buffer .= $helper['if'](
        $data->find('errors.product_detail'), 
        array(
            'name' => 'if',
            'args' => 'if errors.product_detail',
            'hash' => array(),
            'fn' => function($context = null) use ($noop, $data, &$helper) {
                if(is_array($context)) {
                    $data->push($context);
                }

                $buffer = '';
                $buffer .= ' has-error';

                if(is_array($context)) {
                    $data->pop();
                }

                return $buffer;
            },

            'inverse' => $noop
        )
    );

    $buffer .= ' clearfix">'."\n";
    $buffer .= '        <label class="control-label">';
    $buffer .= htmlspecialchars($data->find('_ \'Detail\''), ENT_COMPAT, 'UTF-8');

    $buffer .= '</label>'."\n";
    $buffer .= '        <div>'."\n";
    $buffer .= '            <textarea'."\n";
    $buffer .= '                class="form-control"'."\n";
    $buffer .= '                name="product_detail"'."\n";
    $buffer .= '                placeholder="';
    $buffer .= htmlspecialchars($data->find('_ \'Enter some details about this product.\''), ENT_COMPAT, 'UTF-8');

    $buffer .= '">';
    $buffer .= $data->find('item.product_detail');

    $buffer .= '</textarea>'."\n";
    $buffer .= "\n";
    $buffer .= '            ';
    $buffer .= $helper['if'](
        $data->find('errors.product_detail'), 
        array(
            'name' => 'if',
            'args' => 'if errors.product_detail',
            'hash' => array(),
            'fn' => function($context = null) use ($noop, $data, &$helper) {
                if(is_array($context)) {
                    $data->push($context);
                }

                $buffer = '';
                $buffer .= "\n";
                $buffer .= '            <span class="help-text text-danger">';
                $buffer .= htmlspecialchars($data->find('errors.product_detail'), ENT_COMPAT, 'UTF-8');

                $buffer .= '</span>'."\n";
                $buffer .= '            ';

                if(is_array($context)) {
                    $data->pop();
                }

                return $buffer;
            },

            'inverse' => $noop
        )
    );

    $buffer .= "\n";
    $buffer .= '        </div>'."\n";
    $buffer .= '    </div>'."\n";
    $buffer .= "\n";
    $buffer .= '    <div class="form-group';
    $buffer .= $helper['if'](
        $data->find('errors.product_brand'), 
        array(
            'name' => 'if',
            'args' => 'if errors.product_brand',
            'hash' => array(),
            'fn' => function($context = null) use ($noop, $data, &$helper) {
                if(is_array($context)) {
                    $data->push($context);
                }

                $buffer = '';
                $buffer .= ' has-error';

                if(is_array($context)) {
                    $data->pop();
                }

                return $buffer;
            },

            'inverse' => $noop
        )
    );

    $buffer .= ' clearfix">'."\n";
    $buffer .= '        <label class="control-label">';
    $buffer .= htmlspecialchars($data->find('_ \'Brand\''), ENT_COMPAT, 'UTF-8');

    $buffer .= '</label>'."\n";
    $buffer .= '        <div>'."\n";
    $buffer .= '            <input'."\n";
    $buffer .= '                type="text"'."\n";
    $buffer .= '                class="form-control"'."\n";
    $buffer .= '                name="product_brand"'."\n";
    $buffer .= '                placeholder="';
    $buffer .= htmlspecialchars($data->find('_ \'What brand is this?\''), ENT_COMPAT, 'UTF-8');

    $buffer .= '"'."\n";
    $buffer .= '                value="';
    $buffer .= htmlspecialchars($data->find('item.product_brand'), ENT_COMPAT, 'UTF-8');

    $buffer .= '" />'."\n";
    $buffer .= "\n";
    $buffer .= '            ';
    $buffer .= $helper['if'](
        $data->find('errors.product_brand'), 
        array(
            'name' => 'if',
            'args' => 'if errors.product_brand',
            'hash' => array(),
            'fn' => function($context = null) use ($noop, $data, &$helper) {
                if(is_array($context)) {
                    $data->push($context);
                }

                $buffer = '';
                $buffer .= "\n";
                $buffer .= '            <span class="help-text text-danger">';
                $buffer .= htmlspecialchars($data->find('errors.product_brand'), ENT_COMPAT, 'UTF-8');

                $buffer .= '</span>'."\n";
                $buffer .= '            ';

                if(is_array($context)) {
                    $data->pop();
                }

                return $buffer;
            },

            'inverse' => $noop
        )
    );

    $buffer .= "\n";
    $buffer .= '        </div>'."\n";
    $buffer .= '    </div>'."\n";
    $buffer .= "\n";
    $buffer .= '    <div class="form-group';
    $buffer .= $helper['if'](
        $data->find('errors.product_price'), 
        array(
            'name' => 'if',
            'args' => 'if errors.product_price',
            'hash' => array(),
            'fn' => function($context = null) use ($noop, $data, &$helper) {
                if(is_array($context)) {
                    $data->push($context);
                }

                $buffer = '';
                $buffer .= ' has-error';

                if(is_array($context)) {
                    $data->pop();
                }

                return $buffer;
            },

            'inverse' => $noop
        )
    );

    $buffer .= ' clearfix">'."\n";
    $buffer .= '        <label class="control-label">';
    $buffer .= htmlspecialchars($data->find('_ \'Price\''), ENT_COMPAT, 'UTF-8');

    $buffer .= '</label>'."\n";
    $buffer .= '        <div>'."\n";
    $buffer .= '            <input'."\n";
    $buffer .= '                type="number"'."\n";
    $buffer .= '                class="form-control"'."\n";
    $buffer .= '                name="product_price"'."\n";
    $buffer .= '                min = "0"'."\n";
    $buffer .= '				step = "0.01"'."\n";
    $buffer .= '                placeholder="';
    $buffer .= htmlspecialchars($data->find('_ \'How much do you want to sell it for?\''), ENT_COMPAT, 'UTF-8');

    $buffer .= '"'."\n";
    $buffer .= '                value="';
    $buffer .= htmlspecialchars($data->find('item.product_price'), ENT_COMPAT, 'UTF-8');

    $buffer .= '" />'."\n";
    $buffer .= "\n";
    $buffer .= '            ';
    $buffer .= $helper['if'](
        $data->find('errors.product_price'), 
        array(
            'name' => 'if',
            'args' => 'if errors.product_price',
            'hash' => array(),
            'fn' => function($context = null) use ($noop, $data, &$helper) {
                if(is_array($context)) {
                    $data->push($context);
                }

                $buffer = '';
                $buffer .= "\n";
                $buffer .= '            <span class="help-text text-danger">';
                $buffer .= htmlspecialchars($data->find('errors.product_price'), ENT_COMPAT, 'UTF-8');

                $buffer .= '</span>'."\n";
                $buffer .= '            ';

                if(is_array($context)) {
                    $data->pop();
                }

                return $buffer;
            },

            'inverse' => $noop
        )
    );

    $buffer .= "\n";
    $buffer .= '        </div>'."\n";
    $buffer .= '    </div>'."\n";
    $buffer .= '</div>';