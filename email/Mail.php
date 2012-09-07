<?php
/**
 * This file is part of the Small Neat Box Framework
 * Copyright (c) 2011-2012 Small Neat Box Ltd.
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 */

namespace snb\email;
use snb\email\EmailAbstract;

/**
 * Sends an email using the PHP mail function
 */
class Mail extends EmailAbstract
{
    public function send()
    {
        // Create a bit of text to act as the boundary between the different parts
        $boundary = 'Alt_x'.md5(time().uniqid('').'BandEmail').'x';

        // prepare the headers
        $headers = empty($this->from) ? '' : 'From: '.$this->from['prepared']. "\r\n";
        $headers .= empty($this->from) ? '' : 'Return-Path: '.$this->from['prepared']. "\r\n";
        $headers .= empty($this->replyTo) ? '' : 'Reply-To: '.$this->replyTo['prepared']."\r\n";
        $headers .= empty($this->cc) ? '' : 'CC: '.$this->getCcList()."\r\n";
        $headers .= empty($this->bcc) ? '' : 'BCC: '.$this->getBccList()."\r\n";
        $headers .= "X-Mailer: Band Framework\r\n";
        $headers .= "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: multipart/alternative; boundary=\"$boundary\"\r\n";

        // Start with the plain text version
        $body = "--$boundary\n";
        $body.= "Content-Type: text/plain; charset=\"UTF-8\"\n";
        $body.= "Content-Transfer-Encoding: 7bit\n\n";
        $body.= $this->textBody;
        $body.= "\n\n";

        // then add the HTML version
        $body.= "--$boundary\n";
        $body.= "Content-Type: text/html; charset=\"UTF-8\"\n";
        $body.= "Content-Transfer-Encoding: 7bit\n\n";
        $body.= $this->htmlBody;
        $body.= "\n\n";

        // Add any attachments...
        foreach ($this->attachments as $attachment) {
            $body .= "--$boundary\n";
            $body .= 'Content-Type: '.$attachment['mime'].'; name="'.$attachment['filename'].'"'."\n";
            $body .= "Content-Transfer-Encoding: base64\n";
            $body .= 'Content-Disposition: attachment; filename="'.$attachment['filename'].'"'."\n\n";
            $body .= chunk_split(base64_encode($attachment['content']), 76, "\n");
            $body .= "\n\n";
        }

        // mark the end of the message
        $body.= "--$boundary--\n";

        // Send the message
        mail($this->getToList(), $this->subject, $body, $headers);
    }
}
