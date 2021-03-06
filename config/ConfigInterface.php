<?php
/**
 * This file is part of the Small Neat Box Framework
 * Copyright (c) 2011-2012 Small Neat Box Ltd.
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

namespace snb\config;

interface ConfigInterface
{
    public function get($name, $default = null);
    public function set($name, $value);
    public function has($name);
    public function remove($name);
    public function all();
    public function clearCache();
}
