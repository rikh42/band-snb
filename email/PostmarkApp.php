<?php
/**
 * This file is part of the Small Neat Box Framework
 * Copyright (c) 2011-2012 Small Neat Box Ltd.
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 */

namespace snb\email;
use snb\config\ConfigInterface;
use snb\email\EmailAbstract;
use snb\logger\LoggerInterface;

/**
 * An email handler that talks to Postmark App to provide the actual delivery
 */
class PostmarkApp extends EmailAbstract
{
    protected $config;
    protected $logger;

    /**
     * @param \snb\config\ConfigInterface   $config
     * @param \snb\logger\LoggerInterface $logger
     */
    public function __construct(ConfigInterface $config, LoggerInterface $logger)
    {
        // let the base class do most of the work
        parent::__construct();

        // remember these
        $this->config = $config;
        $this->logger = $logger;
    }

    /**
     * Sends an email, using the Postmark App service
     * @return bool - true if we send the message ok, false if not
     */
    public function send()
    {
        // Make sure curl is installed.
        if (!function_exists('curl_init')) {
            $this->logger->error('Curl not installed - PostmarkApp email requires this');

            return false;
        }

        // Get the URL to talk to postmark app on
        $url = $this->config->get('mail.postmarkapp.url', null);
        if ($url === null) {
            $this->logger->error('Postmark App endpoint URL not defined in config - mail.postmarkapp.url');

            return false;
        }

        // Force the from address to be the registered one...
        $this->from(
            $this->config->get('mail.postmarkapp.from.email', null),
            $this->config->get('mail.postmarkapp.from.name', null));


        // Start to prepare the date, ready for sending to postmark
        $data = array();
        $data['Subject'] = $this->subject;
        $data['HtmlBody'] = $this->htmlBody;
        $data['TextBody'] = $this->textBody;
        $data['From'] = $this->from['prepared'];
        $data['ReplyTo'] = (empty($this->replyTo)) ? $this->from['prepared'] : $this->replyTo['prepared'];
        $data['To'] = $this->getToList();
        $data['Cc'] = $this->getCcList();
        $data['Bcc'] = $this->getBccList();
        $data['Tag'] = $this->tag;

        // output the appropriate data for attachments ($this->attachments)
        if (count($this->attachments) > 0) {
            $data['Attachments'] = array();

            foreach ($this->attachments as $attachment) {
                $data['Attachments'][] = array(
                    'Name' => $attachment['filename'],
                    'Content' => base64_encode($attachment['content']),
                    'ContentType' => $attachment['mime']
                );
            }
        }

        // Get our API token
        $token = $this->config->get('mail.postmarkapp.api_key', 'missing API Key');
        $headers = array(
            'Accept: application/json',
            'Content-Type: application/json',
            'X-Postmark-Server-Token: ' . $token
        );

        // Set up curl to make the POST request
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        // Call Postmark...
        $output = curl_exec($ch);
        $response = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        // See if there was an error
        if ($output === false) {
            $this->logger->error('Unable to send email via Postmark. No response');

            return false;
        }

        // Also check that postmark was happy with the data sent to them
        if (intval($response) != 200) {
            $output = json_decode($output);
            $this->logger->error('Error sending email via Postmark', $output);

            return false;
        }

        // All is well.
        return true;
    }
}
