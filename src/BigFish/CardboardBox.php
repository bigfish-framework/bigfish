<?php
/**
 * BigFish container.
 *
 * The name "CardboardBox" is intended to be a humorous reference to the dance
 * movements "Big Fish, Little Fish, Cardboard Box". The class implements a
 * Dependency Injection Container and is generally instanced as `$app` in the
 * code.
 *
 * - Define a service:  `$app->serviceName = 'className';`
 * - Get a service:     `$service = $app->serviceName;`
 * - Define a parameter:`$app->set('parameterName', $value);`
 * - Get a parameter:   `$parameter = $app->get('parameterName'[, $default]);`
 *
 * Note that parameters can be deep-referenced so `$app->set('my.module.foo',
 * true)` will set the value of `my['module']['foo']` and `$app->get('my')`
 * will return an array. This only works up to 3 deep so
 * `$app->set('my.very.deep.array.element', 1)` will set the parameter
 * `my['very']['deep']['array.element']`.
 *
 * @copyright  Copyright (c) 2015 BigFish
 * @licence    MIT
 */

namespace BigFish;

use BigFish\Exception;

class CardboardBox {
    /** @const string Version number. */
    const VERSION = '0.0.0-dev';

    /** @var array Configuration. */
    protected $_parameters = [];

    /** @var array Defined properties. */
    protected $_properties = [];

    /** @var array Instantiated services. */
    protected $_serviceInstances = [];

    /** @var array Service definitions. */
    protected $_serviceDefinitions = [];

    /**
     * Constructor.
     *
     * @param array Optional parameters to set.
     */
    public function __construct($parameters = null) {
        if ($parameters !== null) {
            $this->set($parameters);
        }
        $this->_serviceDefinitions = $this->get('app.services', []);
    }

    /**
     * Get a service.
     *
     * @param  string  The name of the service.
     * @return object  The requested service.
     */
    public function __get($name) {
        // lazy-load the service
        if (!isset($this->_serviceInstances[$name])) {
            if (!isset($this->_serviceDefinitions[$name])) {
                throw new Exception ("Service [$name] is not defined");
            } else {
                $class = $this->_serviceDefinitions[$name];
                if (!class_exists($class)) {
                    throw new Exception ("Class [$class] for service [$name] does not exist");
                }
            }
            try {
                $service = new $this->_serviceDefinitions[$name]($this, $name);
                // allow the service to replace itself in the constructor
                if (!isset($this->_serviceInstances[$name])) {
                    $this->_serviceInstances[$name] = $service;
                }
            } catch (\Exception $e) {
                throw new Exception("Couldn't load service [$name]: "
                    . lcfirst($e->getMessage()), $e);
            }
        }
        // the service has been instantiated so return it
        return $this->_serviceInstances[$name];
    }

    /**
     * Define a service.
     *
     * @param  string  The name of the property.
     * @param  mixed   The value to set.
     */
    public function __set($name, $value) {
        if (is_string($value)) {
            // set the class name for lazy loading later
            $this->_serviceDefinitions[$name] = $value;
        } else {
            // set this as the service
            $this->_serviceInstances[$name] = $value;
        }
    }

    /**
     * Get a parameter.
     *
     * @param  string  The name of the parameter.
     * @param  mixed   Default (defaults to null).
     * @return mixed   The parameter value or $default if it is not set.
     */
    public function get($name = null, $default = null) {
        if ($name === null) {
            return $this->_parameters;
        } else {
            $parts = explode('.', $name, 4);
            $value = $this->_parameters;
            foreach ($parts as $part) {
                if (array_key_exists($part, $value)) {
                    $value = $value[$part];
                } else {
                    return $default;
                }
            }
            return $value;
        }
    }

    /**
     * Set a parameter.
     *
     * @param  string|array  The name of the parameter or an array of
     *                       name => value pairs.
     * @param  mixed         The value to set it to (ignored for an array).
     * @param  boolean       Flag reversal of name and value arguments.
     * @return $this         So calls can be chained.
     */
    public function set($name, $value = null, $reverse = false) {
        if (is_array($name)) {
            array_walk($name, [$this, 'set'], true);
            return $this;
        }
        if ($reverse) {
            $parts = explode('.', $value, 4);
            $value = $name;
        } else {
            $parts = explode('.', $name, 4);
        }
        $count = count($parts);
        try {
            if ($count < 3) {
                if ($count === 1) {
                    $this->_parameters[$parts[0]] = $value;
                } else {
                    $this->_parameters[$parts[0]][$parts[1]] = $value;
                }
            } else {
                if ($count === 3) {
                    $this->_parameters[$parts[0]][$parts[1]][$parts[2]] = $value;
                } else {
                    $this->_parameters[$parts[0]][$parts[1]][$parts[2]][$parts[3]] = $value;
                }
            }
        } catch (\Exception $e) {
            throw new Exception('CardboardBox parameter error: '.lcfirst($e->getMessage()));
        }
        return $this;
    }
}
