<?php
/**
 * This file is part of the Small Neat Box Framework
 * Copyright (c) 2011-2012 Small Neat Box Ltd.
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

namespace snb\http;

use snb\http\SessionStorageInterface;
use snb\core\ContainerAware;


/**
 * Handles session storage
 */
class SessionStorage extends ContainerAware implements SessionStorageInterface
{
    protected $started;
    protected $writeEnabled;
    protected $flashMessages;
    const FLASH_KEY = '_flashes';



    //==============================
    // __construct
    //==============================
    public function __construct()
    {
        // default values
        $this->started = false;
        $this->writeEnabled = true;
    }

    //==============================
    // start
    // Starts a session
    //==============================
    public function start()
    {
        // If we have already started the session, don't do it again
        if ($this->started) {
            return;
        }

		// Look up some session setting from the config
		$config = $this->container->get('config');
		$lifetime = $config->get('session.lifetime', ini_get('session.cookie_lifetime'));
		$path = $config->get('session.path', ini_get('session.cookie_path'));
		$domain = $config->get('session.domain', ini_get('session.cookie_domain'));
		$secure = $config->get('session.secure', ini_get('session.cookie_secure'));
		$httpOnly = $config->get('session.httponly', ini_get('session.cookie_httponly'));
		session_set_cookie_params($lifetime, $path, $domain, $secure, $httpOnly);

        // Set the name of the session to something other than PHPSESSID
		session_name($config->get('session.name', 'band'));
		session_start();
        $this->started = true;

        // Pull the flash messages out of the session
        $this->flashMessages = array();
        if (isset($_SESSION[self::FLASH_KEY])) {
            $this->flashMessages = $_SESSION[self::FLASH_KEY];
        }

        // clear any pending flash messages
        $_SESSION[self::FLASH_KEY] = array();
    }


    /**
     * Enable or disable writing to the session, where supported by the driver (SessionStorageDB only at present)
     * @param $writeEnable
     */
    public function setWriteEnable($writeEnable)
    {
        $this->writeEnabled = $writeEnable;
    }


    /**
     * Close the session before the end of the script.
     */
    public function closeAndWrite()
    {
        // Allow writing, briefly
        $old = $this->writeEnabled;
        $this->setWriteEnable(true);

        // Stop if we were started
        if ($this->started) {
            session_write_close();
        }

        // make sure we are stopped
        $this->started = false;
        $this->setWriteEnable($old);
    }



    //==============================
    // get
    // Gets a value stored in the session
    //==============================
    public function get($key, $default=null)
    {
        if (array_key_exists($key, $_SESSION)) {
            return $_SESSION[$key];
        }

        return $default;
    }

    //==============================
    // set
    // Sets a value to store it in the session
    //==============================
    public function set($key, $value)
    {
        $_SESSION[$key] = $value;
    }

    //==============================
    // remove
    // removes an item from the session
    //==============================
    public function remove($key)
    {
        if (array_key_exists($key, $_SESSION)) {
            unset($_SESSION[$key]);
        }
    }


    /**
     * @param $name
     * @param $msg
     */
    public function setFlash($name, $msg)
    {
        $_SESSION[self::FLASH_KEY][$name] = $msg;
    }


    /**
     * Removes a flash message that had previously been set using setFlash
     * @param $name
     */
    public function removeFlash($name)
    {
        unset($_SESSION[self::FLASH_KEY][$name]);
    }


    /**
     * @param $name
     * @return bool
     */
    public function hasFlash($name)
    {
        return array_key_exists($name, $this->flashMessages);
    }


    /**
     * @param $name
     * @param null $default
     * @return null]
     */
    public function getFlash($name, $default=null)
    {
        if (array_key_exists($name, $this->flashMessages)) {
            return $this->flashMessages[$name];
        }

        return $default;
    }
}
