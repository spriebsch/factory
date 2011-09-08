<?php

namespace spriebsch\factory\demo\library;

use spriebsch\factory\AbstractFactory;

class Factory extends AbstractFactory
{
    protected $types = array('library_A', 'library_B');

    protected function doGetInstanceFor($type, array $parameters = array())
    {
        switch ($type) {
            case 'library_A':
                return new A(1, 2);
// get params 1 and 2, provide infrastructure for mandatory and optional parameters
            break;

            case 'library_B':
                return new B($this->getInstanceFor('library_A'));
// if this has to be a certain instance (not a type), call doGetInstanceFor instead of getInstenceFor
            break;

            default:
                throw new \Exception('Unknown type "' . $type . '"');
        }
    }
}

class A
{
    protected $a;
    protected $b;

    public function __construct($a, $b)
    {
        $this->a = $a;
        $this->b = $b;
    }

    public function getA()
    {
        return $this->a;
    }

    public function getB()
    {
        return $this->b;
    }
}

class B
{
    protected $a;

    public function __construct(A $a)
    {
        $this->a = $a;
    }

    public function getA()
    {
        return $this->a;
    }
}
