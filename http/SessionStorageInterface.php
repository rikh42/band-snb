<?php
/**
 * This file is part of the Small Neat Box Framework
 * Copyright (c) 2011-2012 Small Neat Box Ltd.
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

namespace snb\http;

interface SessionStorageInterface
{
    public function start();
    public function setWriteEnable($writeEnable);
    public function closeAndWrite();
    public function get($key, $default=null);
    public function set($key, $value);
    public function remove($key);

    public function setFlash($name, $msg);
    public function hasFlash($name);
    public function getFlash($name, $default=null);
}
