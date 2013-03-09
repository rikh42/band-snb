<?php
/**
 * This file is part of the Small Neat Box Framework
 * Copyright (c) 2011-2012 Small Neat Box Ltd.
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */


namespace snb\http;

use snb\http\OutputCacheInterface;
use snb\routing\Route;


/**
 * A dummy output cache that does nothing
 */
class OutputCacheNull implements OutputCacheInterface
{
    public function getResponse(Request $request, $options)
    {
        return null;
    }

    public function cacheResponse(Response $response, $options)
    {
    }
}
