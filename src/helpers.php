<?php
declare(strict_types=1);

use MattyG\Handlebars\Helper;

return array(
    "if" => new Helper\IfHelper(),
    "unless" => new Helper\UnlessHelper(),
    "with" => new Helper\WithHelper(),
    "each" => new Helper\EachHelper(),
    "concat" => new Helper\ConcatHelper(),
    "noop" => new Helper\NoopHelper(),
);
