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
 * The application factory is a facade to all factories.
 * It cannot create any instances itself, but knows which factory is
 * responsible for creating which instance and delegates instantiation.
 *
 * @author Stefan Priebsch <stefan@priebsch.de>
 * @copyright Stefan Priebsch <stefan@priebsch.de>. All rights reserved.
 */
class MasterFactory implements MasterFactoryInterface
{
    /**
     * Associative array tracking which factory is responsible for a type.
     * Array keys are types, array values are references to factory instances.
     *
     * @var array
     */
    private $typeMap = array();

    /**
     * Returns an instance of the requested type by delegating call to
     * responsible child factory instance.
     *
     * @param string $type
     * @return NULL
     */
    public function getInstanceFor($type)
    {
        if (!$this->hasType($type)) {
            throw new FactoryException('Cannot instantiate type "' . $type . '"',
                FactoryException::TYPE_CANNOT_INSTANTIATE);
        }

        $factory = $this->getType($type);
        return call_user_func_array(array($factory, 'getInstanceFor'), func_get_args());
    }

    /**
     * Registers a child factory by setting the master in the child
     * and registering each type the child factory can instantiate.
     *
     * @param ChildFactoryInterface $factory
     * @return NULL
     */
    public function register(ChildFactoryInterface $factory)
    {
        if ($this->isRegistered($factory)) {
            throw new FactoryException('Child factory instance of "' . get_class($factory) . '" is already registered',
                FactoryException::TYPE_ALREADY_REGISTERED);
        }

        $factory->setMaster($this);

        $types = $factory->getTypes();

        if (count($types) == 0) {
            throw new FactoryException('Child Factory instance of "' . get_class($factory) . '" cannot instantiate any types',
                FactoryException::TYPE_NO_TYPES);
        }

        foreach ($types as $type) {
            $this->addType($type, $factory);
        }
    }

    /**
     * Returns an array of types all registered factories are capable of creating.
     * Each value in the array is unique.
     *
     * @returns array
     */
    public function getTypes()
    {
        return array_keys($this->typeMap);
    }

    public function __toString()
    {
        $result = '';
        $reverseMap = array();

        $typeMap = array_map(function($instance) { return get_class($instance); }, $this->typeMap);

        foreach ($typeMap as $key => $value) {
            if (!isset($reverseMap[$value])) {
                $reverseMap[$value] = array();
            }
            $reverseMap[$value][] = $key;
        }

        $result .= 'Master Factory: "' . get_class($this) . '"' . PHP_EOL;

        foreach ($reverseMap as $factory => $types) {
            $result .= PHP_EOL . 'Factory "' . $factory . '" can instantiate the following types:' . PHP_EOL;

            foreach ($types as $type) {
                $result .= '- ' . $type . PHP_EOL;
            }
        }

        return $result . PHP_EOL;
    }
    
    /**
     * Adds a type and a reference to the factory that can instantiate this type
     *
     * @param string $type
     * @param ChildFactoryInterface $factory
     * @return NULL
     */
    protected function addType($type, ChildFactoryInterface $factory)
    {
        $this->typeMap[$type] = $factory;
    }

    /**
     * Checks whether given type is already registered
     *
     * @param string $type
     * @return bool
     */
    protected function hasType($type)
    {
        return array_key_exists($type, $this->typeMap);
    }

    /**
     * Returns the factory instance that can instantiate the given type
     * hasType() check deliberately left out, as it would be untestable due
     * to the "already registered" guard clause in getInstance().
     *
     * @param $type
     * @return NULL
     */
    protected function getType($type)
    {
        return $this->typeMap[$type];
    }
 
    /**
     * Checks whether given factory is already registered
     *
     * @param ChildFactoryInterface $factory
     * @return bool
     */   
    protected function isRegistered(ChildFactoryInterface $factory)
    {
        foreach (array_values($this->typeMap) as $f) {
            if ($factory === $f) {
                return TRUE;
            }
        }
        
        return FALSE;
    }
}
