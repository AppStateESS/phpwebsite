<?php

/**
 * Piwik implementation of Tracker
 * @author Jeff Tickle <jtickle at tux dot appstate dot edu>
 */

PHPWS_Core::initModClass('analytics', 'Tracker.php');

class PiwikTracker extends Tracker
{
    var $piwik_url;
    var $piwik_id;

    public function save()
    {
        $result = parent::save();
        if(PHPWS_Error::isError($result))
            return $result;

        $db = new PHPWS_DB('analytics_tracker_piwik');
        $db->addWhere('id', $this->id);

        $result = $db->select();
        if(PHPWS_Error::logIfError($result))
            return $result;

        $db = new PHPWS_DB('analytics_tracker_piwik');
        $db->addValue('id', $this->id);
        $db->addValue('piwik_url', $this->piwik_url);
        $db->addValue('piwik_id', $this->piwik_id);
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

        $db = new PHPWS_DB('analytics_tracker_piwik');
        $db->addWhere('id', $this->id);
        $result = $db->delete();
        if(PHPWS_Error::logIfError($result))
            return $result;
    }

    public function track()
    {
        $vars = array();
        $vars['PIWIK_URL'] = $this->getPiwikUrl();
        $vars['PIWIK_ID']  = $this->getPiwikId();
        $code = PHPWS_Template::process($vars, 'analytics', 'Piwik/tracker.tpl');

        self::addEndBody($code);
    }

    public function trackerType()
    {
        return 'PiwikTracker';
    }

    public function addForm(PHPWS_Form &$form)
    {
        $form->addText('piwik_url', $this->getPiwikUrl());
        $form->setLabel('piwik_url', dgettext('analytics', 'Base URL of Piwik (DO NOT specify protocol (http:// or https://), as this is autodetected by the script)'));
        $form->setRequired('piwik_url');

        $form->addText('piwik_id', $this->getPiwikId());
        $form->setLabel('piwik_id', dgettext('analytics', 'Piwik Site ID'));
        $form->setRequired('piwik_id');
    }

    public function processForm(array $values)
    {
        parent::processForm($values);
        $this->setPiwikUrl(PHPWS_Text::parseInput($values['piwik_url']));
        $this->setPiwikId(PHPWS_Text::parseInput($values['piwik_id']));
    }

    public function joinDb(PHPWS_DB &$db)
    {
        $db->addJoin('left outer',
            'analytics_tracker', 'analytics_tracker_piwik', 'id', 'id');
        $db->addColumn('analytics_tracker_piwik.piwik_id');
        $db->addColumn('analytics_tracker_piwik.piwik_url');
    }

    public function getFormTemplate()
    {
        return 'Piwik/admin.tpl';
    }

    public function setPiwikUrl($url)
    {
        if(substr($url, -1, 1) != '/') $url .= '/';
        $this->piwik_url = $url;
    }

    public function getPiwikUrl()
    {
        return $this->piwik_url;
    }

    public function setPiwikId($id)
    {
        $this->piwik_id = $id;
    }

    public function getPiwikId()
    {
        return $this->piwik_id;
    }
}

?>
