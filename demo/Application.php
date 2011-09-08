<?php

namespace spriebsch\factory\demo\application;

/**
 * Offers an explicit API to create object instances
 */
class ServiceLocator
{
    /**
     * @var MasterFactoryInterface
     */
    protected $factory;

    /**
     * Constructs the object
     *
     * @param MasterFactoryInterface $factory
     * @return NULL
     */
    public function __construct($factory)
    {
        $this->factory = $factory;
    }

    /**
     * ServiceA is some library class that has no constructor parameters
     */
    public function getServiceA()
    {
        return $this->factory->getInstanceFor('library_A');
    }

    /**
     * ServiceB is a framework class that requires an additional constructor parameter
     */
    public function getServiceB()
    {
        return $this->factory->getInstanceFor('framework_Y', 'foo');
    }

    public function getServiceC()
    {
        return $this->factory->getInstanceFor('framework_X');
    }

    public function getServiceD()
    {
        return $this->factory->getInstanceFor('library_B');
    }
}
