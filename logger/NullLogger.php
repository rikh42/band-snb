<?php
/**
 * This file is part of the Small Neat Box Framework
 * Copyright (c) 2011-2012 Small Neat Box Ltd.
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

namespace snb\logger;
use snb\logger\LoggerInterface;

class NullLogger implements LoggerInterface
{
    public function __construct(HandlerInterface $handler = null)
    {
    }

    public function dump()
    {
    }

    public function getLog()
    {
        return '';
    }

    public function getHtmlLog()
    {
        return '';
    }

    /**
     * Adds a debug level message to the log
     * @param $message
     * @param null $extraData
     */
    public function debug($message, $extraData = null)
    {
    }

    /**
     * Log an info level message (ie, something that we want to know about
     * but is normal behaviour, such as a user logging in)
     * @param $message
     * @param null $extraData
     */
    public function info($message, $extraData = null)
    {
    }

    /**
     * Logs a warning message. Normally used when something has gone wrong,
     * but we can deal with it
     * @param $message
     * @param null $extraData
     */
    public function warning($message, $extraData = null)
    {
    }

    /**
     * Log an error message. This should be used when something serious has gone
     * wrong that is hard to recover from, like key config files missing, database
     * is down etc.
     * @param $message
     * @param null $extraData
     */
    public function error($message, $extraData = null)
    {
    }

    /**
     * Call to log the memory usage at the time of the call. The message
     * should provide some indication of what was going on and where you are calling from
     * @param $message
     */
    public function logMemory($message)
    {
    }

    /**
     * Called to log a Query to the database. We store the query, arguments and
     * timing data in the log. At display time we will also attempt to make calls
     * to EXPLAIN, in order to extract extra data about the query.
     * @param $message
     * @param $sql
     * @param $args
     * @param $queryTime
     */
    public function logQuery($message, $sql, $args, $queryTime)
    {
    }

    /**
     * Logs the time at the point of the call. The message should include enough
     * information to figure out where the call was made. Useful for find slow
     * spots in the code.
     * @param $message
     */
    public function logTime($message)
    {
    }
}
