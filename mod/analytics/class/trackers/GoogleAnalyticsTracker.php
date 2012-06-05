<?php

/**
 * Google Analytics implementation of Tracker
 * @author Jeff Tickle <jtickle at tux dot appstate dot edu>
 */

PHPWS_Core::initModClass('analytics', 'Tracker.php');

class GoogleAnalyticsTracker extends Tracker
{
    var $account;

    public function save()
    {
        $result = parent::save();
        if(PHPWS_Error::isError($result))
            return $result;

        $db = new PHPWS_DB('analytics_tracker_google');
        $db->addWhere('id', $this->id);

        $result = $db->select();
        if(PHPWS_Error::logIfError($result))
            return $result;

        $db = new PHPWS_DB('analytics_tracker_google');
        $db->addValue('id', $this->id);
        $db->addValue('account', $this->account);
        if(count($result) < 1) {
            $result = $db->insert(false);
        } else {
            $result = $db->update();
        }
        if(PHPWS_Error::logIfError($result))
            return $result;
    }

    public function delete()
    {
        $result = parent::delete();
        if(PHPWS_Error::isError($result))
            return $result;

        $db = new PHPWS_DB('analytics_tracker_google');
        $db->addWhere('id', $this->id);
        $result = $db->delete();
        if(PHPWS_Error::logIfError($result))
            return $result;
    }

    public function track()
    {
        $vars = array();
        $vars['TRACKER_ID'] = $this->getAccount();
        $code = PHPWS_Template::process($vars, 'analytics', 'GoogleAnalytics/tracker.tpl');

        self::addEndBody($code);
    }

    public function trackerType()
    {
        return 'GoogleAnalyticsTracker';
    }

    public function addForm(PHPWS_Form &$form)
    {
        $form->addText('account', $this->getAccount());
        $form->setLabel('account', dgettext('analytics', 'Account Identifier (ie, UA-XXXXXXXX-X)'));
        $form->setRequired('account');
    }

    public function processForm(array $values)
    {
        parent::processForm($values);
        $this->setAccount(PHPWS_Text::parseInput($values['account']));
    }

    public function joinDb(PHPWS_DB &$db)
    {
        $db->addJoin('left outer', 
            'analytics_tracker', 'analytics_tracker_google', 'id', 'id');
        $db->addColumn('analytics_tracker_google.account');
    }

    public function getFormTemplate()
    {
        return 'GoogleAnalytics/admin.tpl';
    }

    public function setAccount($account)
    {
        $this->account = $account;
    }

    public function getAccount()
    {
        return $this->account;
    }
}

?>
