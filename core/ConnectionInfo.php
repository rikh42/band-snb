<?php
/**
 * This file is part of the Small Neat Box Framework
 * Copyright (c) 2011-2012 Small Neat Box Ltd.
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

namespace snb\core;
use \PDO;

//=====================================
// ConnectionInfo
// Information about a database connection
//=====================================
class ConnectionInfo
{
    protected $host = '';
    protected $port = '';
    protected $user = '';
    protected $password = '';
    protected $database = '';
    protected $charset = '';
    protected $pdo = null;

    public function __construct($host, $port, $user, $password, $database, $charset='')
    {
        $this->host = $host;
        $this->port = $port;
        $this->user = $user;
        $this->password = $password;
        $this->database = $database;
        $this->charset = $charset;
    }

    public function setServer($host, $port)
    {
        $this->host = $host;
        $this->port = $port;
        $this->pdo = null;
    }

    public function setUser($user, $password)
    {
        $this->user = $user;
        $this->password = $password;
        $this->pdo = null;
    }

    public function setDatabase($database)
    {
        $this->database = $database;
        $this->pdo = null;
    }

    public function setCharset($charset)
    {
        $this->charset = $charset;
        $this->pdo = null;
    }

    public function setPDO(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function getPDO()
    {
        return $this->pdo;
    }

    public function resetPDO()
    {
        $this->pdo = null;
    }

    public function getConnectionString()
    {
        $items = array();
        if (!empty($this->host)) {
            $items[] = "host={$this->host}";
        }

        if (!empty($this->port)) {
            $items[] = "port={$this->port}";
        }

        if (!empty($this->database)) {
            $items[] = "dbname={$this->database}";
        }

        if (!empty($this->charset)) {
            $items[] = "charset={$this->charset}";
        }

        $cs = implode(';', $items);
        return 'mysql:'.$cs;
    }

    public function getUsername()
    {
        return $this->user;
    }

    public function getPassword()
    {
        return $this->password;
    }
}
