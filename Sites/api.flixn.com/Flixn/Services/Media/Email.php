<?php
/**
 * Flixn
 *
 * @category    Flixn
 * @package     Services
 *
 * @author      Samuel J. Greear
 * @copyright   Copyright (c) 2008 Flixn, Inc.
 * @version     $Id $
 */

require('Ex/Mail/MailSend.php');

class FlixnServicesMediaEmail
{
    public function __construct()
	{
        $this->MIMEBoundary = md5(time());

        $this->MSGContainer = "--" . $this->MIMEBoundary . "\r\n" .
                              "Content-Type: text/plain; charset=iso-8859-1\r\n" .
                              "Content-Transfer-Encoding: 8bit\r\n" .
                              "\r\n%s\r\n\r\n" .
                              "--" . $this->MIMEBoundary . "\r\n" .
                              "Content-Type: text/html; charset=iso-8859-1\r\n" .
                              "Content-Transfer-Encoding: 8bit\r\n" .
                              "\r\n%s\r\n\r\n" .
                              "--" . $this->MIMEBoundary . "--\r\n\r\n";

$this->TEXTMessage = <<<MSG
Message:
%s

You can view this video at:
%s
MSG;

$this->HTMLMessage = <<<MSG

MSG;

$this->Subject = <<<SUBJECT
%s has sent you a video
SUBJECT;

$this->From = <<<FROM
%s <%s>
FROM;

    }

    public function Send($from, $to_addr, $message, $url)
	{
		$from_addr = 'webmaster@flixn.com';
		
        $subject = sprintf($this->Subject, $from, $url);
        $from = sprintf($this->From, $from, $from_addr);

        $text_message = sprintf($this->TEXTMessage, $message, $url);
        $html_message = $this->HTMLMessage;

        $message = sprintf($this->MSGContainer, $text_message, $html_message);

        $sm = new MailSend($from, $subject, $text_message);
//        $sm = new SendMail($from, $subject, $message);

        $sm->AddHeader('Return-To', $from_addr);
        $sm->AddHeader('Return-Path', $from_addr);
        $sm->AddHeader('Message-ID', '<' . $this->MIMEBoundary . '@flixn.com>');
        $sm->AddHeader('X-Mailer', 'PHP v' . phpversion());

//        $sm->AddHeader('MIME-Version', '1.0');
//        $sm->AddHeader('Content-Type', 'multipart/alternative; boundary="' . $this->MIMEBoundary . '"');

        $sm->AddRecipient($to_addr);

        $sm->Send();
        unset($sm);
    }
}