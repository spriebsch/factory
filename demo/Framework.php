<?php

namespace spriebsch\factory\demo\framework;

use spriebsch\factory\AbstractFactory;
use Exception;

/**
 * Wraps the original framework factory
 */
class Factory extends AbstractFactory
{
    /**
     * @var OriginalFactory
     */
    protected $originalFactory;
    
    /**
     * @var array
     */
    protected $types = array('framework_X', 'framework_Y');

    /**
     * Constructs the object
     *
     * @param OriginalFactory $factory
     * @return NULL
     */
    public function __construct(OriginalFactory $factory)
    {
        $this->originalFactory = $factory;
    }

    /**
     * Returns an instance of given type, passing given parameters to the constructor
     *
     * @param string $type
     * @param array $params
     * @return object
     */
    protected function doGetInstanceFor($type, array $params = array())
    {
        switch ($type) {
            case 'framework_X':
                return $this->originalFactory->getInstance('spriebsch\\factory\\demo\\framework\\X', $this->getParams());

            case 'framework_Y':
                return $this->originalFactory->getInstance('spriebsch\\factory\\demo\\framework\\Y', $this->getParams());

            default:
                throw new Exception('Unknown type "' . $type . '"');
        }
    }
}

class OriginalFactory
{
    public function getInstance($class)
    {
        switch ($class) {
            case 'spriebsch\\factory\\demo\\framework\\X';
                return new X();
            case 'spriebsch\\factory\\demo\\framework\\Y';
                return new Y();
        }
    }
}

class X
{
}

class Y
{
}
