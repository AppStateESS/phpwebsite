<?php

/**
 * Frontend class for Pear's Mail class.
 * Settings are in core/config/mail_settings.php
 *
 * Usage:
 *
 * require_once 'core/class/Mail.php';
 *
 * // All addresses can contain a proper name or just the address itself
 *
 * $send_to  = 'John Smith <jsmith@fake.address.com>';
 * // You can also use an array
 * //$send_to[] = 'Laura Jones <Jones@happy.fun.com>';
 * //$send_to[] = 'John Smith <jsmith@fake.address.com>';
 *
 * $subject  = 'Testing my email abilities.';
 * $from     = 'your_friend@UCCU.edu';
 * $reply_to = 'Department@UCCU.edu';           // optional
 * $carbon   = 'corndog@fake.address.com';      // optional, can be an array
 * $blind    = 'deep_throat@nixon_library.com'; // optional, can be an array
 * $message  = 'Hello. This is a test message. This message is subject to change during testing.';
 *
 *
 * $mail = new PHPWS_Mail;
 *
 * $mail->addSendTo($send_to);
 * $mail->setSubject($subject);
 * $mail->setFrom($from);
 * $mail->setReplyTo($reply_to);
 * $mail->addCarbonCopy($carbon);
 * $mail->addBlindCopy($blind);
 * $mail->setMessageBody($message);
 * $result = $mail->send();
 *
 * result will either be TRUE or a Pear error object
 * 
 * @version $Id$
 * @author Matthew McNaney <mcnaney at gmail dot com>
 */

PHPWS_Core::requireConfig('core', 'mail_settings.php');

class PHPWS_Mail {
    public $send_to           = array();
    public $subject_line      = null;
    public $from_address      = null;
    public $reply_to_address  = null;
    public $carbon_copy       = null;
    public $blind_copy        = null;
    public $message_body      = null;
    public $html_body         = null;
    public $message_tpl       = null;
    public $send_individually = true;
    public $backend_type      = MAIL_BACKEND;

    public function addSendTo($address)
    {
        return $this->_addAddress('send_to', $address);
    }

    public function sendIndividually($send=true)
    {
        $this->send_individually = (bool)$send;
    }

    public function setSubject($subject_line)
    {
        $this->subject_line = strip_tags($subject_line);
    }

    public function setFrom($from_address)
    {
        if ($this->checkAddress($from_address)) {
            $this->from_address = $from_address;
            return TRUE;
        } else {
            return FALSE;
        }
    }

    public function setReplyTo($reply_to_address)
    {
        if ($this->checkAddress($reply_to_address)) {
            $this->reply_to_address = $reply_to_address;
            return TRUE;
        } else {
            return FALSE;
        }
    }

    public function addCarbonCopy($address)
    {
        return $this->_addAddress('carbon_copy', $address);
    }

    public function addBlindCopy($address)
    {
        return $this->_addAddress('blind_copy', $address);
    }

    public function _addAddress($variable_name, $address)
    {
        $failures = array();

        if (is_array($address)) {
            foreach ($address as $address_item) {
                if (!$this->_addAddress($variable_name, $address_item)) {
                    $failures[] = $address_item;
                }
            }
        } else {
            if (!$this->checkAddress($address)) {
                return FALSE;
            } else {
                $this->{$variable_name}[] = $address;
                return TRUE;
            }
        }

        if (!empty($failures)) {
            return $failures;
        } else {
            return TRUE;
        }
    }

    public function setHTMLBody($html_body)
    {
        $this->html_body = $html_body;
    }

    public function setMessageBody($message_body)
    {
        $this->message_body = $message_body;
    }

    public function setBackend($backend)
    {
        if ($backend == 'sendmail' || $backend == 'mail' || $backend == 'smtp') {
            $this->backend_type = $backend;
            return TRUE;
        }

        return FALSE;
    }


    /**
     * Check the validity of the an email address. 
     * Must contain only word characters, spaces, less than,
     * more then, periods, at symbol and dashes
     * If address contains a newline character, it will be refused
     */
    public function checkAddress($email_address)
    {
        if ( preg_match('/\n|\r/', $email_address) ) {
            return FALSE;
        }
        
        if ( substr_count($email_address, '@') != 1 ) {
            return FALSE;
        }
        
        return !preg_match('/[^\w\s<>\.@\-\'"]/', $email_address);
    }
    
    /**
     * @returns If sent individually and an error occurs, a false statement 
     *          is returned and the error is logged after the mailing is complete.
     *          If sent altogether, the error itself is returned. If all goes well,
     *          true is returned.
     */
    public function send()
    {
        $param = array();

        require_once 'Mail.php';
        require_once 'Mail/mime.php';

        if (empty($this->send_to) || empty($this->from_address) || 
            ( empty($this->message_body) && empty($this->html_body) ) ) {
            return FALSE;
        }

        $message = new Mail_mime();
        if (!empty($this->message_body)) {
            $message->setTXTBody($this->message_body);
        }

        if (!empty($this->html_body)) {
            $message->setHTMLBody($this->html_body);
        }

        $body = $message->get();

        $headers['From']    = & $this->from_address;
        $headers['Subject'] = & $this->subject_line;

        if (isset($this->reply_to_address)) {
            $headers['Reply-To'] = $this->reply_to_address;
        }

        if (!empty($this->carbon_copy)) {
            $headers['Cc'] = implode(',', $this->carbon_copy);
        }

        if (!empty($this->blind_copy)) {
            $headers['Bcc'] = implode(',', $this->blind_copy);
        }

        switch ($this->backend_type) {
        case 'mail':
            break;

        case 'sendmail':
            if (defined('SENDMAIL_PATH')) {
                $param['sendmail_path'] = SENDMAIL_PATH;
            }

            break;

        case 'smtp':
            if (!defined('SMTP_HOST') || !defined('SMTP_PORT')) {
                return FALSE;
            }
            
            if ( !defined('SMTP_AUTH') || 
                 ( SMTP_AUTH && (!defined('SMTP_USER') || !defined('SMTP_PASS')) ) ) {
                return FALSE;
            }

            $param['host'] = SMTP_HOST;
            $param['port'] = SMTP_PORT;

            if (SMTP_AUTH) {
                $param['auth'] = true;
                $param['username'] = SMTP_USER;
                $param['password'] = SMTP_PASS;
            } else {
                $param['auth'] = false;
            }
            break;

        }

        $m_headers = $message->headers($headers);
        $mail_object = Mail::factory($this->backend_type, $param);

        if ($this->send_individually) {
            foreach($this->send_to as $address) {
                $recipients['To']   = $address;
                $result = $mail_object->send($recipients, $m_headers, $body);
                if (PHPWS_Error::logIfError($result)) {
                    $error_found = true;
                }
            }
            if (isset($error_found)) {
                return false;
            } else {
                return true;
            }
        } else {
            $recipients['To']   = implode(',', $this->send_to);
            return $mail_object->send($recipients, $m_headers, $body);
        }
    }

}

?>