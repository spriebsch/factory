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

namespace spriebsch\factory;

/**
 * The child factory is responsible for creating object instances of given type.
 *
 * @author Stefan Priebsch <stefan@priebsch.de>
 * @copyright Stefan Priebsch <stefan@priebsch.de>. All rights reserved.
 */
abstract class AbstractFactory implements ChildFactoryInterface
{
    /**
     * Master factory to ask for types the factory cannot instantiate
     *
     * @var MasterFactoryInterface
     */
    protected $masterFactory;

    /**
     * Array of types the factory can instantiate
     *
     * @var array
     */
    protected $types = array();

    /**
     * Returns instance of given type
     *
     * @param string $type
     * @return object
     */
    final public function getInstanceFor($type)
    {
        if (!in_array($type, $this->types)) {
            if ($this->masterFactory === NULL) {
                throw new FactoryException('Cannot instantiate type "' . $type . '"',
                    FactoryException::TYPE_CANNOT_INSTANTIATE);
            }

            // we don't know this type, ask master factory
            return call_user_func_array(array($this->masterFactory, 'getInstanceFor'), func_get_args());
        }

        $result = call_user_func(array($this, 'doGetInstanceFor'), $type, array_slice(func_get_args(), 1));

        if (!is_object($result)) {
            throw new FactoryException('Method doGetInstanceFor() did not return object',
                FactoryException::TYPE_ILLEGAL_RETURN_VALUE);
        }

        return $result;
    }

    /**
     * Set the master factory
     *
     * @param MasterFactoryInterface $factory
     * @return NULL
     */
    public function setMaster(MasterFactoryInterface $factory)
    {
        $this->masterFactory = $factory;
    }

    /**
     * Returns the master factory
     *
     * @return MasterFactoryInterface
     */
    public function getMaster()
    {
        return $this->masterFactory;
    }

    /**
     * Returns an array of all types the factory is capable of creating
     *
     * @return NULL
     */
    public function getTypes()
    {
        return $this->types;
    }

    /**
     * Registers a type the factory is capable of instantiating
     *
     * @param string $type
     * @return NULL
     */
    protected function registerType($type)
    {
        $this->types[] = $type;
    }

    /**
     * Returns an object instance of given type, passing the additional
     * parameters as constructor parameters.
     *
     * @param string $type
     * @param array $parameters
     * @return object
     */
    abstract protected function doGetInstanceFor($type, array $parameters = array());
}
