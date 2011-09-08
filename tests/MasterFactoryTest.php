<?php
/**
 * Copyright (c) 2011 Stefan Priebsch <stefan@priebsch.de>
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without modification,
 * are permitted provided that the following conditions are met:
 *
 *   * Redistributions of source code must retain the above copyright notice,
 *     this list of conditions and the following disclaimer.
 *
 *   * Redistributions in binary form must reproduce the above copyright notice,
 *     this list of conditions and the following disclaimer in the documentation
 *     and/or other materials provided with the distribution.
 *
 *   * Neither the name of Stefan Priebsch nor the names of contributors
 *     may be used to endorse or promote products derived from this software
 *     without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
 * AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO,
 * THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR
 * PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER ORCONTRIBUTORS
 * BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY,
 * OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF
 * SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS
 * INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN
 * CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE)
 * ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
 *
 * @package    factory
 * @author     Stefan Priebsch <stefan@priebsch.de>
 * @copyright  Stefan Priebsch <stefan@priebsch.de>. All rights reserved.
 * @license    BSD License
 */

namespace spriebsch\factory\tests;

use PHPUnit_Framework_TestCase;
use spriebsch\factory\MasterFactory;
use spriebsch\factory\tests\stubs\FactoryStub;
use spriebsch\factory\tests\stubs\ObjectStub;

/**
 * Unit tests for the MasterFactory class.
 *
 * @author Stefan Priebsch <stefan@priebsch.de>
 * @copyright Stefan Priebsch <stefan@priebsch.de>. All rights reserved.
 */
class MasterFactoryTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var spriebsch\factory\MasterFactory
     */
    protected $factory;

    /**
     * @var spriebsch\factory\ChildFactoryInterface
     */
    protected $childFactory;

    /**
     * @var string
     */
    protected $type = 'a-valid-type';

    /**
     * Prepares the test fixture.
     *
     * @return NULL
     */
    protected function setUp()
    {
        $this->factory = new MasterFactory();
        $this->childFactory = $this->getMock('spriebsch\\factory\\ChildFactoryInterface');
    }
    
    /**
     * Destroys the test fixture.
     *
     * @return NULL
     */
    protected function tearDown()
    {
        unset($this->factory);
        unset($this->childFactory);
    }

    /**
     * Helper method to make setTypes() in the child factory return given type
     *
     * @return NULL
     */
    protected function setGetTypesExpectation()
    {
        $this->childFactory->expects($this->once())
                           ->method('getTypes')
                           ->will($this->returnValue(array($this->type)));
    }

    /**
     * Makes sure that getTypes() initially returns an emtpy array
     *
     * @covers spriebsch\factory\MasterFactory::getTypes
     */
    public function testGetTypesInitiallyReturnsEmptyArray()
    {
        $this->assertEquals(array(), $this->factory->getTypes());
    }

    /**
     * Makes sure that getTypes() returns an array of types, and the array
     * contains the merged values of all registered child factories.
     *
     * @covers spriebsch\factory\MasterFactory::getTypes
     * @covers spriebsch\factory\MasterFactory::addType
     */
    public function testGetTypesReturnsArrayOfTypes()
    {
        $this->setGetTypesExpectation();
        $this->factory->register($this->childFactory);
        
        $this->secondChildFactory = $this->getMock('spriebsch\\factory\\ChildFactoryInterface');
        $this->secondChildFactory->expects($this->once())
                                 ->method('getTypes')
                                 ->will($this->returnValue(array('another-valid-type')));
        $this->factory->register($this->secondChildFactory);

        $this->assertEquals(array('a-valid-type', 'another-valid-type'), $this->factory->getTypes());
    }

    /**
     * Makes sure an exception is thrown when trying to register the same
     * factory instance twice.
     *
     * @covers spriebsch\factory\MasterFactory::register
     * @covers spriebsch\factory\MasterFactory::isRegistered
     *
     * @expectedException spriebsch\factory\FactoryException
     * @expectedExceptionCode spriebsch\factory\FactoryException::TYPE_ALREADY_REGISTERED
     */
    public function testRegisterThrowsExceptionWhenFactoryInstanceIsAlreadyRegistered()
    {
        $this->setGetTypesExpectation();

        $this->factory->register($this->childFactory);
        $this->factory->register($this->childFactory);
    }

    /**
     * Register a child and makes sure that the master calls getTypes()
     *
     * @covers spriebsch\factory\MasterFactory::register
     */
    public function testRegisterGetsTypesFromChildFactory()
    {
        $this->setGetTypesExpectation();

        $this->factory->register($this->childFactory);
    }

    /**
     * Makes sure an exception is thrown when trying to instantiate invalid type
     *
     * @covers spriebsch\factory\MasterFactory::getInstanceFor
     * @covers spriebsch\factory\MasterFactory::hasType
     *
     * @expectedException spriebsch\factory\FactoryException
     * @expectedExceptionCode spriebsch\factory\FactoryException::TYPE_CANNOT_INSTANTIATE
     */
    public function testGetInstanceForThrowsExceptionOnUnknownType()
    {
        $this->factory->getInstanceFor('this-is-not-a-valid-type');
    }

    /**
     * Makes sure an exception is thrown when child factory cannot instantiate
     * any types at all.
     *
     * @covers spriebsch\factory\MasterFactory::register
     *
     * @expectedException spriebsch\factory\FactoryException
     * @expectedExceptionCode spriebsch\factory\FactoryException::TYPE_NO_TYPES
     */
    public function testRegisterThrowsExceptionWhenChildFactoryCannotInstantiateAnyTypes()
    {
        $this->childFactory->expects($this->once())
                           ->method('getTypes')
                           ->will($this->returnValue(array()));

        $this->factory->register($this->childFactory);
    }

    /**
     * Registers a child factory and makes sure the master factory is set in it.
     *
     * @covers spriebsch\factory\MasterFactory::register
     */
    public function testRegisterSetsMasterInChildFactory()
    {
        $this->childFactory = new FactoryStub();
        $this->childFactory->registerType($this->type);
        $this->factory->register($this->childFactory);

        $this->assertSame($this->factory, $this->childFactory->getMaster());
    }

    /**
     * Registers a child factory that returns a stub object and makes sure
     * that master factory calls getInstanceFor() in child factory.
     *
     * @covers spriebsch\factory\MasterFactory::getInstanceFor
     */
    public function testGetInstanceForDelegatesToChildFactory()
    {
        $this->setGetTypesExpectation();

        $this->childFactory->expects($this->once())
                           ->method('getInstanceFor')
                           ->with($this->type);

        $this->factory->register($this->childFactory);

        $this->factory->getInstanceFor($this->type);
    }

    /**
     * Makes sure getInstance() returns an object instance
     *
     * @covers spriebsch\factory\MasterFactory::getInstanceFor
     * @covers spriebsch\factory\MasterFactory::getType
     */
    public function testGetInstanceReturnsObjectInstance()
    {
        $stub = new ObjectStub();

        $this->setGetTypesExpectation();

        $this->childFactory->expects($this->once())
                           ->method('getInstanceFor')
                           ->with($this->type)
                           ->will($this->returnValue($stub));

        $this->factory->register($this->childFactory);

        $this->assertSame($stub, $this->factory->getInstanceFor($this->type));
    }
    
    /**
     * Makes sure that __toString() output contains master factory class name
     *
     * @covers spriebsch\factory\MasterFactory::__toString
     */
    public function testToStringOutputContainsFactoryClassName()
    {
        $this->assertContains(get_class($this->factory), (string) $this->factory);
    }

    /**
     * Makes sure that __toString() output contains child factory class name
     *
     * @covers spriebsch\factory\MasterFactory::__toString
     */
    public function testToStringOutputContainsChildFactoryClassNames()
    {
        $this->setGetTypesExpectation();
        $this->factory->register($this->childFactory);
        $this->assertContains(get_class($this->childFactory), (string) $this->factory);
    }

    /**
     * Makes sure that __toString() output contains type
     *
     * @covers spriebsch\factory\MasterFactory::__toString
     */
    public function testToStringOutputContainsTypesRegisteredByChildFactories()
    {
        $this->setGetTypesExpectation();
        $this->factory->register($this->childFactory);
        $this->assertContains($this->type, (string) $this->factory);
    }
}
