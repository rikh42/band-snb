<?php
/**
 * This file is part of the Small Neat Box Framework
 * Copyright (c) 2011-2012 Small Neat Box Ltd.
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 */

namespace snb\email;

interface EmailInterface
{
    /**
     * @param $subject
     * @return EmailInterface
     */
    public function subject($subject);

    /**
     * @param $email
     * @param null $name
     * @return EmailInterface
     */
    public function to($email, $name=null);

    /**
     * @param $email
     * @param null $name
     * @return EmailInterface
     */
    public function cc($email, $name=null);

    /**
     * @param $email
     * @param null $name
     * @return EmailInterface
     */
    public function bcc($email, $name=null);

    /**
     * @param $email
     * @param null $name
     * @return EmailInterface
     */
    public function from($email, $name=null);

    /**
     * @param $email
     * @param null $name
     * @return EmailInterface
     */
    public function replyTo($email, $name = null);

    /**
     * @param $html
     * @return EmailInterface
     */
    public function htmlBody($html);

    /**
     * @param $plain
     * @return EmailInterface
     */
    public function textBody($plain);

    /**
     * @param $filename
     * @param string $mime
     * @return EmailInterface
     */
    public function attach($filename, $mime='application/octet-stream');

    /**
     * @param $tag
     * @return EmailInterface
     */
    public function tag($tag);

    /**
     * @return bool
     */
    public function send();

    public function getHtmlBody();
    public function getTextBody();
}
