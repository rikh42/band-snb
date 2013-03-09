<?php
/**
 * This file is part of the Small Neat Box Framework
 * Copyright (c) 2011-2012 Small Neat Box Ltd.
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */


namespace snb\http;

use snb\routing\Route;
use snb\http\Response;


/**
 * An interface called by the kernel to determine if the page
 * should be stored in the output cache
 */
interface OutputCacheInterface
{
    public function getResponse(Request $request, $options);
    public function cacheResponse(Response $response, $options);
}