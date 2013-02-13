<?php
/**
 * This file is part of the Small Neat Box Framework
 * Copyright (c) 2011-2012 Small Neat Box Ltd.
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

namespace snb\form\filters;
use snb\form\filters\FilterInterface;

/**
 * A simple filter that trims white space from fields
 */
class TrimFilter implements FilterInterface
{
    public function in($value)
    {
        return trim($value);
    }

    public function out($value)
    {
        return trim($value);
    }
}
