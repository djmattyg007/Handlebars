<?php
declare(strict_types=1);

namespace MattyG\Handlebars;

abstract class Template
{
    /**
     * @var Runtime
     */
    protected $runtime;

    /**
     * @var DataFactory
     */
    protected $dataFactory;

    /**
     * @param Runtime $runtime
     * @param DataFactory $dataFactory
     */
    public function __construct(Runtime $runtime, DataFactory $dataFactory)
    {
        $this->runtime = $runtime;
        $this->dataFactory = $dataFactory;
    }

    /**
     * Alias __invoke() to render(), making the object a callable.
     *
     * @param array $context
     * @return string The rendered template
     */
    public function __invoke(array $context = array()) : string
    {
        return $this->render($context);
    }

    /**
     * @param array $context
     * @return string The rendered template
     */
    public function render(array $context = array()) : string
    {
        $data = $this->dataFactory->create($context);
        return $this->_render($data);
    }

    /**
     * @param Data $data
     * @return string
     */
    abstract protected function _render(Data $data) : string;
}
