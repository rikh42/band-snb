<?php
    /**
     * This file is part of the Small Neat Box Framework
     * Copyright (c) 2011-2012 Small Neat Box Ltd.
     * For the full copyright and license information, please view the LICENSE.txt
     * file that was distributed with this source code.
     */

namespace snb\http;

use snb\http\Response;



/**
 * RedirectResponse
 * A special response object that sets up for a redirection
 */
class RedirectResponse extends Response
{

    /**
     * @param string $redirectUrl - The URL to redirect to
     * @param int $responseCode - The response code (defaults to 302 - Found)
     */
    public function __construct($redirectUrl, $responseCode = 302)
    {
        // set up the regular response object
        parent::__construct('', $responseCode);

        // Set the redirection URL
        $this->setRedirectToURL($redirectUrl);
    }
}