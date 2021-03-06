<?php
/**
 * This file is part of the Small Neat Box Framework
 * Copyright (c) 2011-2012 Small Neat Box Ltd.
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

namespace snb\core;

use snb\core\ContainerInterface;
use snb\exceptions\CircularReferenceException;

/**
 * Service Container
 * This class holds all the services that might be available to an app
 * A service is just a class that acts like a singleton
 * eg. $container->get('database'); // return the one and only database engine
 */
class ServiceContainer implements ContainerInterface
{
    protected $services;

    public function __construct()
    {
        $this->services = array();
    }

    /**
     * set
     * Sets the named service
     * @param string $name   - the name of the service
     * @param mixed  $object - either the classname of the object, or the instance of the object
     */
    public function set($name, $object)
    {
        $this->services[$name] = $object;
    }

    /**
     * setMany
     * Adds an array of services to the container, in one go
     * @param array $list - a list of name/value pairs of services to add
     */
    public function setMany($list)
    {
        if (is_array($list)) {
            foreach ($list as $name=>$object) {
                $this->set($name, $object);
            }
        }
    }



	/**
	 * get
	 * Get the named service object. If it has not been created yet, it will be created
	 * @param $name
	 * @return null|object|ContainerAware
	 * @throws \snb\exceptions\CircularReferenceException
	 */
	public function get($name)
    {
        // if the feature exists, return it
        if (array_key_exists($name, $this->services)) {
            // get the named object
            $object = $this->services[$name];

			// see if it is an alias to another service
			if (is_string($object)) {
				return $this->get($object);
			}

            // If the object has already been created and set up, return it.
            if (!($object instanceof ServiceDefinition)) {
                return $object;
			}

            // prevent circular references
            if ($object->isCreating) {
                throw new CircularReferenceException($name);
            }

            // create the object
            $object->setContainer($this);
            $object->isCreating = true;
            $instance = $object->create($this);

            // If this is a singleton then replace the ServiceDefinition with the new instance
            if ($object->isSingleton()) {
                $this->services[$name] = $instance;
			}

            $object->isCreating = false;

            return $instance;
        }

        // else nothing
        return null;
    }



    /**
     * Performs a wildcard search for services.
     * eg "twig.extension.*"
     * @param $name
     * @return array of all the services that matched the wildcard
     */
    public function getMatching($name)
    {
        // If there is no wildcard, just treat as get
        if (strpos($name, '*')===false) {
            return array($this->get($name));
		}

        // There are wildcards in there, so built a regex and search
        $regex = '/'.str_replace('*', '[^.]+', $name).'/';
        $matching = array();
        foreach ($this->services as $service => $ref) {
            if (preg_match($regex, $service)) {
                // yes, this is a match, so we want to return this item
                $matching[] = $this->get($service);
            }
        }

        return $matching;
    }

    /**
     * Magic get function, so you can also go $container->database
     * @param $name
     * @return object|null
     */
    public function __get($name)
    {
        return $this->get($name);
    }

}
