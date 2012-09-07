<?php
/**
 * This file is part of the Small Neat Box Framework
 * Copyright (c) 2011-2012 Small Neat Box Ltd.
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

namespace snb\core;
use snb\core\AutoLoaderInterface;


/**
 * Something to contain the AutoLoader class. This makes it accessible
 * Around the application, despite the service container not being ready
 * at the time its created. It also avoids leaking a variable out into
 * the global namespace.
 */
class AutoLoadContainer
{
    /**
     * @var \snb\core\AutoLoaderInterface
     */
    protected static $loader;


    /**
     * Registers the auto loader to use
     * @static
     * @param AutoLoaderInterface $loader
     */
    public static function register(AutoLoaderInterface $loader)
    {
        static::$loader = $loader;
        static::$loader->register();

    }


    /**
     * Adds a set of namespaces to the auto loader
     * @static
     * @param $namespaces
     */
    public static function addNamespaces($namespaces)
    {
        static::$loader->registerNamespaces($namespaces);
    }


    /**
     * Adds a list of PEAR like Prefixes to the auto loader
     * @static
     * @param $prefixes
     */
    public static function addPrefixes($prefixes)
    {
        static::$loader->registerPrefixes($prefixes);
    }

    public static function addMappings($mappings)
    {
        static::$loader->registerMappings($mappings);
    }
}

