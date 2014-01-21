<?php

namespace Properties;

/**
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @license http://opensource.org/licenses/gpl-3.0.html
 */
class Report {

    public $id;
    public $message_id;
    public $date_sent;
    public $reason = null;
    public $message;
    public $reporter_id;
    public $offender_id;
    public $block = 0;
    public $block_reason;

    public function __construct($id=0)
    {
        if ($id) {
            $this->setId($id);
            if (!$this->init()) {
                $this->id = 0;
            }
        }
    }

    private function init()
    {
        $db = new \PHPWS_DB('prop_report');
        $db->addWhere('id', $this->id);
        return $db->loadObject($this);
    }

    public function setId($id)
    {
        $this->id = (int) $id;
    }

    public function setMessageId($id)
    {
        $this->message_id = (int) $id;
    }

    public function setReason($reason)
    {
        $this->reason = strip_tags($reason);
    }

    public function getReason()
    {
        return nl2br($this->reason);
    }

    public function save()
    {
        $db = new \PHPWS_DB('prop_report');
        if (!$this->id) {
            $this->date_sent = time();
        }
        return $db->saveObject($this);
    }

    public function setBlockReason($reason)
    {
        $this->block_reason = strip_tags($reason);
    }

    public function getDate()
    {
        return date('g:ma M j, Y', $this->date_sent);
    }

    public function row()
    {
        $tpl['DATE_SENT'] = $this->getDate();
        $tpl['MESSAGE'] = substr($this->message, 0, 30);
        $tpl['REASON'] = substr($this->reason, 0, 30);
        $tpl['ID'] = $this->id;
        return $tpl;
    }

    public function view()
    {
        $tpl['DATE_SENT'] = $this->getDate();
        $tpl['MESSAGE'] = nl2br($this->message);
        $tpl['REASON'] = $this->getReason();

        $reporter = new \PHPWS_User($this->reporter_id);
        $offender = new \PHPWS_User($this->offender_id);

        $tpl['REPORTER'] = $reporter->getUsername();
        $tpl['OFFENDER'] = $offender->getUsername();

        $vars['QUESTION'] = "Ignoring this report will permanently delete it. Be sure you have dealt with those involved before removing it.\nAre you sure you want to ignore this report?";
        $vars['ADDRESS'] = \PHPWS_Text::linkAddress('properties', array('aop' => 'ignore_report', 'id' => $this->id), true);
        $vars['LINK'] = 'Ignore';
        $tpl['IGNORE'] = javascript('confirm', $vars);

        if (!$this->block) {
            $vars['QUESTION'] = "Ignoring this report will permanently delete it. Be sure you have dealt with those involved before removing it.\nAre you sure you want to ignore this report?";
            $vars['ADDRESS'] = \PHPWS_Text::linkAddress('properties', array('aop' => 'ignore_report', 'id' => $this->id), true);
            $vars['LINK'] = 'Ignore';
            $links[] = javascript('confirm', $vars);

            $vars['QUESTION'] = "Blocking a report will prevent the offender from logging in. Are you sure you want to do this?";
            $vars['ADDRESS'] = \PHPWS_Text::linkAddress('properties', array('aop' => 'block_report', 'id' => $this->id), true);
            $vars['LINK'] = 'Block';
            $links[] = javascript('confirm', $vars);
        } else {
            $vars['QUESTION'] = 'Removing this block will allow ' . $offender->getUsername() . ' access to roommates again. Are you sure you want to do this?';
            $vars['ADDRESS'] = \PHPWS_Text::linkAddress('properties', array('aop' => 'ignore_report', 'id' => $this->id), true);
            $vars['LINK'] = 'Remove Block';
            $links[] = javascript('confirm', $vars);
        }

        $links[] = '<a style="cursor : pointer" id="close-view">Close</a>';

        if ($this->block) {
            $tpl['BLOCK_REASON'] = $this->block_reason;
        }

        $tpl['LINKS'] = implode(' | ', $links);

        return \PHPWS_Template::process($tpl, 'properties', 'report_view.tpl');
    }

    public function delete()
    {
        $db = new \PHPWS_DB('prop_report');
        $db->addWhere('id', $this->id);
        $result = $db->delete();
        if (\PHPWS_Error::isError($result)) {
            \PHPWS_Error::log($result);
            throw new Exception('Could not delete report');
        }
        return true;
    }

    public function setReporterId($id)
    {
        $this->reporter_id = (int) $id;
    }

    public function setOffenderId($id)
    {
        $this->offender_id = (int) $id;
    }

}

?>
