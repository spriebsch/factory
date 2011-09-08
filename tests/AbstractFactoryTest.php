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
use spriebsch\factory\tests\stubs\FactoryStub;
use spriebsch\factory\tests\stubs\ObjectStub;

/**
 * Unit tests for the AbstractFactory class.
 *
 * @author Stefan Priebsch <stefan@priebsch.de>
 * @copyright Stefan Priebsch <stefan@priebsch.de>. All rights reserved.
 */
class AbstractFactoryTest extends PHPUnit_Framework_TestCase
{
    /**
     * Prepares the test fixture.
     *
     * @return NULL
     */
    protected function setUp()
    {
        $this->factory = new FactoryStub();
    }
    
    /**
     * Destroys the test fixture.
     *
     * @return NULL
     */
    protected function tearDown()
    {
        unset($this->factory);
    }

    /**
     * @covers spriebsch\factory\AbstractFactory::getInstanceFor
     */
    public function testGetInstanceForReturnsInstance()
    {
        $this->object = new ObjectStub();

        $this->factory->setInstance($this->object);
        $this->factory->registerType('some-type');

        $this->assertSame($this->object, $this->factory->getInstanceFor('some-type'));
    }

    /**
     * @covers spriebsch\factory\AbstractFactory::getInstanceFor
     * @expectedException spriebsch\factory\FactoryException
     * @expectedExceptionCode spriebsch\factory\FactoryException::TYPE_CANNOT_INSTANTIATE
     */
    public function testGetInstanceForThrowsExceptionOnUnknownType()
    {
        $this->factory->getInstanceFor('this-type-does-not-exist');
    }

    /**
     * @covers spriebsch\factory\AbstractFactory::getInstanceFor
     * @covers spriebsch\factory\AbstractFactory::registerType
     * @expectedException spriebsch\factory\FactoryException
     * @expectedExceptionCode spriebsch\factory\FactoryException::TYPE_ILLEGAL_RETURN_VALUE
     */
    public function testGetInstanceForThrowsExceptionWhenDoGetInstanceForReturnsNonObject()
    {
        $type = 'some-type';

        $this->factory->setInstance('this-is-not-an-object');
        $this->factory->registerType($type);

        $this->factory->getInstanceFor($type);
    }

    /**
     * @covers spriebsch\factory\AbstractFactory::setMaster
     * @covers spriebsch\factory\AbstractFactory::getInstanceFor
     */
    public function testGetInstanceForDelegatesToMasterFactoryOnUnknownType()
    {
        $type = 'some-type';

        $this->master = $this->getMock('spriebsch\\factory\\MasterFactoryInterface');

        $this->master->expects($this->once())
                     ->method('getInstanceFor')
                     ->with($type);

        $this->factory->setMaster($this->master);
        $this->factory->getInstanceFor($type);
    }

    /**
     * @covers spriebsch\factory\AbstractFactory::registerType
     * @covers spriebsch\factory\AbstractFactory::getTypes
     */
    public function testGetTypesReturnsArrayOfTypes()
    {
        $type1 = 'some-type';
        $type2 = 'some-other-type';

        $this->factory->registerType($type1);
        $this->factory->registerType($type2);

        $types = $this->factory->getTypes();
        $this->assertInternalType('array', $types);
        $this->assertEquals(2, count($types));
        $this->assertContains($type1, $types);
        $this->assertContains($type2, $types);
    }
}
