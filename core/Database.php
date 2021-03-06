<?php
/**
 * This file is part of the Small Neat Box Framework
 * Copyright (c) 2011-2012 Small Neat Box Ltd.
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

namespace snb\core;
use snb\core\DatabaseInterface;
use snb\core\ConnectionInfo;
use snb\core\ContainerAware;
use snb\logger\LoggerInterface;

use \PDO;
use \PDOException;



/**
 * The core Database management layer
 */
class Database extends ContainerAware implements DatabaseInterface
{
    /**
     * @var \PDO
     */
    protected $pdo = null;

    // info about the last query
    protected $lastQuery;
    protected $lastResult;
    protected $lastInsertID;
    protected $lastRowsAffected;

    // Connection info
    protected $connections;
    protected $activeConnection;
    protected $inTransaction;

    /**
     * @var \snb\logger\LoggerInterface
     */
    protected $logger;

    // how do we want to fetch the data
    const FETCH_ALL = 1;
    const FETCH_ROW = 2;
    const FETCH_ONE = 3;




    /**
     * attempts to initialise the database connection
     */
    public function __construct()
    {
        // set some defaults
        $this->lastQuery = '';
        $this->lastResult = null;
        $this->lastInsertID = 0;

        $this->connections = array();
        $this->activeConnection = 'none';
        $this->inTransaction = false;
        $this->logger = null;
    }

    /**
     * Called by the service container on construction
     * sets up a single DB connection, called "read".
     */
    public function init()
    {
        $this->logger = $this->container->get('logger');
        $this->addConnection('read');
        $this->setActiveConnection('read');
    }




    /**
     * Adds information about a connection to the database object
     * but does not do anything with it
     * @param $name
     */
    public function addConnection($name)
    {
        // get the container
        $c = $this->container;
        if ($c == null) {
            return;
		}

        // access the config settings
        $config = $c->get('config');

        // Find out about the database connection info
        $key = 'database.'.$name.'.';
        $host = $config->get($key.'host', 'localhost');
        $port = $config->get($key.'port', '3306');
        $user = $config->get($key.'user', 'root');
        $password = $config->get($key.'password', '');
        $database = $config->get($key.'database', 'test');

        // add the connection info to the set of available connections
        $this->connections[$name] = new ConnectionInfo($host, $port, $user, $password, $database);
        $this->logger->debug("Added connection info to Database handler", array('host'=>$host, 'user'=>$user, 'database'=>$database));
    }



    /**
     * makes the named connection the active one, connecting to the database if required.
     * @param $name
     * @return bool
     */
    public function setActiveConnection($name)
    {
        // If there isn't a connection of that name defined, fail
        if (!array_key_exists($name, $this->connections)) {
            return false;
		}

        // forget the existing connection and note the active one
        $this->pdo = null;
        $this->activeConnection = $name;

        // we won't actually connect to the database until someone tries to make a query on it
        return true;
    }



    /**
     * Call this before accessing the pdo object.
     * This basically performs a lazy connection to the database
     * @throws \RuntimeException
     * @throws \PDOException
     */
    protected function enableActiveConnection()
    {
        if ($this->pdo != null) {
            return;
		}

        // If we are in the middle of a transaction, don't allow the connection to be changed
        if ($this->inTransaction) {
            throw new \RuntimeException("Attempt to change database connection while in the middle of a transaction");
        }

        // We have a connection. Has it already been set up?
        /* @var $info ConnectionInfo */
        $info = $this->connections[$this->activeConnection];
        if ($info->getPDO() == null) {
            // now try and reconnect
            try {
                // create the PDO data object
                $cs = $info->getConnectionString();
                $user = $info->getUsername();
                $password = $info->getPassword();
                $this->logger->info('Database: Connecting to database', $cs);
                $pdo = new PDO($cs, $user, $password, array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"));

                // Set up the connection how we like it
                $pdo->setAttribute(PDO::ATTR_CASE, PDO::CASE_NATURAL);
                $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

                // save it for later
                $info->setPDO($pdo);
            } catch (PDOException $e) {
                $this->logger->error('Database: Connection to '.$this->activeConnection.' failed: ' . $e->getMessage());
                throw $e;
            }
        }

        // Make this the active connection
        $this->pdo = $info->getPDO();
    }


    /**
     * Clears the last results etc at the start of a new query
     */
    protected function resetResults()
    {
        // reset a few things
        $this->lastInsertID = 0;
        $this->lastResult = null;
        $this->lastRowsAffected = 0;
    }



    /**
     * returns true if there is a valid connection to a database
     * As connections are only created when needed (ie when a the first query is performed)
     * this will always return false until after an attempt to make a query.
     * @return bool
     */
    public function isOpenConnection()
    {
        return ($this->pdo != null);
    }


    /**
     * Close all the connections. This is not needed in a normal web application.
     * However, if you are working with an application that runs for long periods
     * (for example a command line queue processor), then you may need to close the connections
     * to prevent them timing out and falling into a broken state.
     */
    public function closeAllConnections()
    {
        // get rid of the connection we are using
        $this->pdo = null;

        // loop over all the connections we have and ensure they are all closed.
        foreach ($this->connections as $connection)
        {
            // Get rid of the cached connections as well
            /* @var $connection ConnectionInfo */
            $connection->resetPDO();
        }
    }




    /**
     * binds the named params in the query to values passed in
     * eg. $params['int:iUser'] = 5; would bind the value 5 to the named argument :iUser as an integer
     * @param \PDOStatement $statement
     * @param $query
     * @param $params
     */
    protected function autoBind(\PDOStatement $statement, $query, $params)
    {
        // If the params passed in is an array, bind its contents, else ignore it
        if (!is_array($params)) {
            return;
		}

        // We need to find all the items in the query that look like potential binds
        // query is in the form 'select sql blar x = :varname, y=:otherName'
        // valid characters are a-z A-Z 0-9 and _ (from the PDO source, as it isn't in the docs)
        if (preg_match_all('/:[a-z0-9_]+/iu', $query, $toBind)===false) {
            return;
		}

        // This list of bound variable names in the query can now be found in $toBind[0]...

        // Try and match the arguments to the list
        foreach ($params as $key=>$value) {
			// If the value is a DateTime object, then convert it to a MySQL friendly string
			if ($value instanceof \DateTime) {
				$value = $value->format('YmdHis');
			}

            // check each parameter and bind any that appear valid
            if (preg_match('/^([a-z]+)(:[a-z0-9_]+)$/iu', $key, $regs)) {
                // Check that this var is in the list of items needing to be bound
                if (!in_array($regs[2], $toBind[0])) {
                    continue;
				}

                // it is, so process the type and bind it
                $typeName = mb_strtolower($regs[1]);
                switch ($typeName) {
                    case 'text':
                    case 'date':
                        $type = PDO::PARAM_STR;
                        break;

                    case 'money':
                        $type = PDO::PARAM_STR;
                        $value = strval(round(floatval($value), 2));
                        break;

                    case 'int':
                    default:
                        $type = PDO::PARAM_INT;
                        break;

                }

                // bind the value to the statement
                $statement->bindValue($regs[2], $value, $type);
            } elseif (preg_match('/^([a-z0-9_]+)$/iu', $key)) {
                // Check that key (eg iUser) is in the binding list (eg :iUser)
                if (!in_array(':'.$key, $toBind[0])) {
                    continue;
				}

                // assume they are using the simpler format of $params['iUser'] = 5
                // we don't know the type in this situation, so we can just bind away
                $statement->bindValue($key, $value);
            }
        }
    }

    /**
     * Help function to handle all the select queries
     * @param $query
     * @param $params
     * @param $fetchMethod - One of FETCH_ALL, FETCH_ROW or FETCH_ONE
     * @return bool|null
     * @throws \RuntimeException
     */
    protected function selectQuery($query, $params, $fetchMethod)
    {
        try {
            // Start the clock
            $startTime = microtime(true);

            // if the db connection failed, fail right back...
            $this->enableActiveConnection();
            if (!$this->pdo) {
                return null;
			}

            // prepare a query, bind a value to it and execute it
            $this->resetResults();
            $results = false;
            $this->lastQuery = $query;
            $stmt = $this->pdo->prepare($query);
            if ($stmt) {
                $this->autoBind($stmt, $query, $params);
                if ($stmt->execute()) {
                    // pull the data from the statement
                    switch ($fetchMethod) {
                        case self::FETCH_ALL:
                            $results = $stmt->fetchAll(PDO::FETCH_OBJ);
                            break;

                        case self::FETCH_ROW:
                            $results = $stmt->fetch(PDO::FETCH_OBJ);
                            break;

                        case self::FETCH_ONE:
                        default:
                            $results = $stmt->fetchColumn(0);
                            break;
                    }
                } else {
                    $this->logger->error('Query Failed to execute', array('query'=>$query, 'args'=>$params));
                }

                // Finished with the statement now
                $stmt = null;
            } else {
                $this->logger->error('Query Failed to prepare', array('query'=>$query, 'args'=>$params));
            }

            // return the results or null
            if ($results === false) {
                $results = null;
            }

            $this->lastResult = $results;
            $this->logger->logQuery('SQL Select', $query, $params, microtime(true)-$startTime);

            return $results;
        } catch (PDOException $e) {
            $this->logger->error('Query Failed. PDO threw an exception: '.$e->getMessage(), array('query'=>$query, 'args'=>$params));

            throw new \RuntimeException("PDO Query Failed: $query", 0, $e);
        }
    }



    /**
     * Get all the results back from a query.
     * @param $query - SQL Query
     * @param array|null $params
     * @return null|array
     */
    public function all($query, $params=null)
    {
        return $this->selectQuery($query, $params, self::FETCH_ALL);
    }




    /**
     * Get a single row of data back from the query
     * @param $query - The SQL Query
     * @param array|null $params
     * @return stdClass|null
     */
    public function row($query, $params=null)
    {
        return $this->selectQuery($query, $params, self::FETCH_ROW);
    }



    /**
     * Get a single value back from the query
     * @param $query - An SQL Query
     * @param array|null $params
     * @return mixed|null
     */
    public function one($query, $params=null)
    {
        return $this->selectQuery($query, $params, self::FETCH_ONE);
    }



    /**
     * general insert or update query
     * @param $query
     * @param array|null $params
     * @return int
     * @throws \RuntimeException
     */
    public function query($query, $params=null)
    {
        try {
            // Start the clock
            $startTime = microtime(true);

            // if the db connection failed, fail right back...
            $this->enableActiveConnection();
            if (!$this->pdo) {
                return 0;
			}

            // prepare a query, bind a value to it and execute it
            $this->resetResults();
            $this->lastQuery = $query;
            $stmt = $this->pdo->prepare($query);
            if ($stmt) {
                $this->autoBind($stmt, $query, $params);
                if ($stmt->execute()) {
                    // Try and grab the resulting insert id
                    $this->lastInsertID = $this->pdo->lastInsertId();
                    $this->lastRowsAffected = $stmt->rowCount();
                }

                // Finished with the statement now
                $stmt = null;
            }

            $this->logger->logQuery('Generic SQL Query', $query, $params, microtime(true)-$startTime);

            return $this->lastRowsAffected;
        } catch (PDOException $e) {
            $this->logger->error('Query Failed. PDO threw an exception: '.$e->getMessage(), array('query'=>$query, 'args'=>$params));

            throw new \RuntimeException("PDO Query Failed", 0, $e);
        }
    }



    /**
     * gets the index of the row inserted in the last query.
     * It is reset to 0 with every query made to the database, so you will need to get
     * this value right away if you need it.
     * @return int
     */
    public function getLastInsertID()
    {
        return (int) $this->lastInsertID;
    }


    /**
     * gets the index of the row inserted in the last query as a string.
     * @return int
     */
    public function getLastInsertIDString()
    {
        return $this->lastInsertID;
    }


    /**
     * Starts a transaction on the database.
     * You must call commitTransaction() or rollBackTransaction() soon after calling this...
     * @return bool
     * @throws \RuntimeException
     */
    public function beginTransaction()
    {
        // if the db connection failed, fail right back...
        $this->enableActiveConnection();
        if (!$this->pdo) {
            // we can't start a transaction without a PDO connection
            // throwing an exception is more likely to prevent the actual queries from being executed...
            throw new \RuntimeException("Tried to start a transaction without a valid connection");
        }

        // check we are not already in a transaction
        if ($this->inTransaction) {
            throw new \RuntimeException("beginTransaction called when we are already in a transaction");
        }

        // actually begin the transaction
        $result = $this->pdo->beginTransaction();
        if ($result) {
            $this->inTransaction = true;
        }

        // return success or failure
        return $result;
    }


    /**
     * commits a transaction started with beginTransaction
     * @return bool
     * @throws \RuntimeException
     */
    public function commitTransaction()
    {
        // check we are already in a transaction
        if (!$this->inTransaction) {
            throw new \RuntimeException("commitTransaction called without a matching beginTransaction()");
        }

        $this->inTransaction = false;
        return $this->pdo->commit();
    }


    /**
     * Rolls back a transaction in case of error
     * @return bool
     * @throws \RuntimeException
     */
    public function rollBackTransaction()
    {
        // check we are already in a transaction
        if (!$this->inTransaction) {
            throw new \RuntimeException("commitTransaction called without a matching beginTransaction()");
        }

        $this->inTransaction = false;
        return $this->pdo->rollBack();
    }
}
