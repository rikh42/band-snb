<?php
/**
 * This file is part of the Small Neat Box Framework
 * Copyright (c) 2011-2012 Small Neat Box Ltd.
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

namespace snb\core;

/**
 * AutoLoader
 * Loads in classes based on their namespace and name
 */
interface AutoLoaderInterface
{
    public function registerNamespaces($namespaces);
    public function registerPrefixes($prefixes);
    public function registerMappings($mappings);
    public function register();


//    public function getNamespaces();
 //    public function loadClass($class);
}
