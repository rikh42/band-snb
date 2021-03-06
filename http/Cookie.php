<?php
/**
 * This file is part of the Small Neat Box Framework
 * Copyright (c) 2011-2012 Small Neat Box Ltd.
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

namespace snb\http;

//==============================
// Cookie
// A cookie
//==============================
class Cookie
{
    public $name;
    public $value;
    public $expire;
    public $path;
    public $domain;
    public $secure;
    public $httpOnly;

    /**
     * @param $name - the name of the cookie
     * @param $value - the value to store in the cookie
     * @param $expire - The timestamp of when to expire the cookie. 0=end of session. <0 means delete cookie
     * @param string $path     - the path that the cookie is limited to
     * @param null   $domain
     * @param bool   $secure   - true to only send this cookie over https connections
     * @param bool   $httpOnly - true to prevent cookie being accessible to javascript on client
     */
    public function __construct($name, $value, $expire=-1, $path='/', $domain=null, $secure=false, $httpOnly=true)
    {
        // strip out characters that can not be stored in cookies
        $name = preg_replace('/[=,; \t\r\n\013\014]/m', '', $name);
        $value = preg_replace('/[=,; \t\r\n\013\014]/m', '', $value);

        // validate some values
        $expire = (int) $expire;

        // Less than zero means delete cookie
        // Zero means keep for session
        // Greater than zero expire at the timestamp given
        if ($expire<0) {
            // a negative value is treated as "in the past"
            // pick a time that is a month ago to be sure of it being "out of date"
            // really, this should delete the cookie on the client
            $expire = time() - (60*60*24*30);
        }

        // store the values
        $this->name = $name;
        $this->value = $value;
        $this->expire = $expire;
        $this->path = empty($path) ? '/' : $path;
        $this->domain = $domain;
        $this->secure = (bool) $secure;
        $this->httpOnly = (bool) $httpOnly;
    }

    /**
     * Sets the cookie in the headers
     */
    public function setCookie()
    {
        setcookie($this->name, $this->value, $this->expire, $this->path, $this->domain, $this->secure, $this->httpOnly);
    }
}
