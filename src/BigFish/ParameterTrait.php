<?php
/**
 * BigFish request.
 *
 * @copyright  Copyright (c) 2015 BigFish
 * @licence    MIT
 */

namespace BigFish;

trait ParameterTrait { // implements \IteratorAggregate and \Countable

    /**
     * Parameter storage.
     *
     * @var array
     */
    protected $_parameters = [];


    /**
     * Parameter storage.
     *
     * @var array
     */
    protected $_parametersName;

    /**
     * Returns the parameter keys.
     *
     * @return array An array of parameter keys
     */
    public function keys() {
        return array_keys($this->_parameters);
    }

    /**
     * Returns a parameter by name.
     *
     * @param string $path    The key
     * @param mixed  $default The default value if the parameter key does not exist
     *
     * @return mixed
     */
    public function get($path = null, $default = null, $throw = false) {
        if ($path === null) {
            return $this->_parameters;
        } elseif (array_key_exists($path, $this->_parameters)) {
            return $this->_parameters[$path];
        } elseif ($throw) {
            if ($this->_parametersName === null) {
                throw new \RangeException("Parameter $path does not exist");
            } else {
                throw new \RangeException("Parameter $path does not exist in the $this->_parametersName parameters");
            }
        } else {
            return $default;
        }
    }

    /**
     * Returns true if the parameter is defined.
     *
     * @param string $key The key
     *
     * @return bool true if the parameter exists, false otherwise
     */
    public function has($key) {
        return array_key_exists($key, $this->_parameters);
    }

    /**
     * Returns an iterator for parameters.
     *
     * @return \ArrayIterator An \ArrayIterator instance
     */
    public function getIterator() {
        return new \ArrayIterator($this->_parameters);
    }

    /**
     * Returns the number of parameters.
     *
     * @return int The number of parameters
     */
    public function count() {
        return count($this->_parameters);
    }
    protected function setParameters($parameters) {
        $this->_parameters = $parameters;
    }
}
