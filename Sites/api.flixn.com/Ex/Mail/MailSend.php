<?php
/**
 * Exhibition
 *
 * @category    Exhibition
 * @package     Mail
 * 
 * @author      Samuel J. Greear
 * @copyright   Copyright (c) 2005-2006 Samuel J. Greear
 * @copyright   Copyright (c) 2007-2008 Evilcode Corporation
 * @version     $Id $
 */

class MailSend {

    public $To;
    public $Subject;
    public $Message;
    private $Headers;

    public function __construct($from, $subject, $message) {

        $this->To = array();
        $this->Headers = array();

        $this->Subject = $subject;
        $this->Message = $message;

        $this->AddHeader('From', $from);
    }

    public function AddHeader($name, $value) {
        $this->Headers[$name] = $value;
    }

    public function AddHeaders($arr) {
        foreach ($arr as $key => $value)
            $this->AddHeader($key, $value);
    }

    public function AddRecipient($address) {
        $this->To[] = $address;
    }

    public function AddRecipients($arr) {
        foreach($arr as $address)
            $this->AddRecipient($address);
    }

    public function SetMessage($message) {
        $this->Message = $message;
    }

    public function Send() {

        $headers = '';
        foreach ($this->Headers as $key => $value)
            $headers .= sprintf("$key: $value\r\n");


        if (count($this->To) == 0)
            throw new Exception('No recipients set');

        $to = '';
        foreach ($this->To as $value)
            $to .= "$value, ";
        $to = substr($to, 0, -2);

        mail($to, $this->Subject, $this->Message, $headers);
    }
}