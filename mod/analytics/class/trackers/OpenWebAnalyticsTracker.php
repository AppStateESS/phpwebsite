<?php

/**
 * Open Web Analytics implementation of Tracker
 * @author Jeff Tickle <jtickle at tux dot appstate dot edu>
 */

PHPWS_Core::initModClass('analytics', 'Tracker.php');

class OpenWebAnalyticsTracker extends Tracker
{
    var $owa_url;
    var $owa_site_id;
    var $owa_track_page_view = 1;
    var $owa_track_clicks = 1;
    var $owa_track_domstream = 1;

    public function save()
    {
        $result = parent::save();
        if(PHPWS_Error::isError($result))
            return $result;

        $db = new PHPWS_DB('analytics_tracker_owa');
        $db->addWhere('id', $this->id);
        
        $result = $db->select();
        if(PHPWS_Error::logIfError($result))
            return $result;

        $db = new PHPWS_DB('analytics_tracker_owa');
        $db->addValue('id', $this->id);
        $db->addValue('owa_url', $this->owa_url);
        $db->addValue('owa_site_id', $this->owa_site_id);
        $db->addValue('owa_track_page_view', $this->owa_track_page_view);
        $db->addValue('owa_track_clicks', $this->owa_track_clicks);
        $db->addValue('owa_track_domstream', $this->owa_track_domstream);
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

        $db = new PHPWS_DB('analytics_tracker_owa');
        $db->addWhere('id', $this->id);
        $result = $db->delete();
        if(PHPWS_Error::logIfError($result))
            return $result;
    }

    public function track()
    {
        $vars = array();
        $vars['OWA_URL'] = $this->getOwaUrl();
        $vars['OWA_SITE_ID'] = $this->getOwaSiteId();

        $vars['OWA_CMDS'] = array();
        
        if($this->getTrackPageView())
            $vars['OWA_CMDS'][]['OWA_CMD'] = 'trackPageView';

        if($this->getTrackClicks())
            $vars['OWA_CMDS'][]['OWA_CMD'] = 'trackClicks';

        if($this->getTrackDomStream())
            $vars['OWA_CMDS'][]['OWA_CMD'] = 'trackDomStream';

        $code = PHPWS_Template::process($vars, 'analytics', 'OpenWebAnalytics/tracker.tpl');

        self::addEndBody($code);
    }

    public function trackerType()
    {
        return 'OpenWebAnalyticsTracker';
    }

    public function addForm(PHPWS_Form &$form)
    {
        $form->addText('owa_url', $this->getOwaUrl());
        $form->setLabel('owa_url', dgettext('analytics', 'Base URL of Open Web Analytics (DO specify protocol (http:// or https://)'));
        $form->setRequired('owa_url');

        $form->addText('owa_site_id', $this->getOwaSiteId());
        $form->setLabel('owa_site_id', dgettext('analytics', 'Open Web Analytics Site ID'));
        $form->setRequired('owa_site_id');

        $form->addCheck('owa_track_page_view', 1);
        $form->setMatch('owa_track_page_view', $this->getTrackPageView());
        $form->setLabel('owa_track_page_view', dgettext('analytics', 'Track Page Views (see OWA documentation)'));
        
        $form->addCheck('owa_track_clicks', 1);
        $form->setMatch('owa_track_clicks', $this->getTrackClicks());
        $form->setLabel('owa_track_clicks', dgettext('analytics', 'Track Clicks (see OWA documentation)'));

        $form->addCheck('owa_track_domstream', 1);
        $form->setMatch('owa_track_domstream', $this->getTrackDomStream());
        $form->setLabel('owa_track_domstream', dgettext('analytics', 'Track DOM Stream (see OWA documentation)'));
    }

    public function processForm(array $values)
    {
        parent::processForm($values);
        $this->setOwaUrl(PHPWS_Text::parseInput($values['owa_url']));
        $this->setOwaSiteId(PHPWS_Text::parseInput($values['owa_site_id']));

        if(isset($values['owa_track_page_view']))
            $this->setTrackPageView(true);
        else
            $this->setTrackPageView(false);

        if(isset($values['owa_track_clicks']))
            $this->setTrackClicks(true);
        else
            $this->setTrackClicks(false);

        if(isset($values['owa_track_domstream']))
            $this->setTrackDomStream(true);
        else
            $this->setTrackDomStream(false);
    }

    public function joinDb(PHPWS_DB &$db)
    {
        $db->addJoin('left outer',
            'analytics_tracker', 'analytics_tracker_owa', 'id', 'id');
        $db->addColumn('analytics_tracker_owa.owa_url');
        $db->addColumn('analytics_tracker_owa.owa_site_id');
        $db->addColumn('analytics_tracker_owa.owa_track_page_view');
        $db->addColumn('analytics_tracker_owa.owa_track_clicks');
        $db->addColumn('analytics_tracker_owa.owa_track_domstream');
    }

    public function getFormTemplate()
    {
        return 'OpenWebAnalytics/admin.tpl';
    }

    public function setOwaUrl($url)
    {
        $this->owa_url = $url;
    }

    public function getOwaUrl()
    {
        return $this->owa_url;
    }

    public function setOwaSiteId($id)
    {
        $this->owa_site_id = $id;
    }

    public function getOwaSiteId()
    {
        return $this->owa_site_id;
    }

    public function setTrackPageView($track)
    {
        $this->owa_track_page_view = $track;
    }

    public function getTrackPageView()
    {
        return $this->owa_track_page_view;
    }

    public function setTrackClicks($track)
    {
        $this->owa_track_clicks = $track;
    }

    public function getTrackClicks()
    {
        return $this->owa_track_clicks;
    }

    public function setTrackDomStream($track)
    {
        $this->owa_track_domstream = $track;
    }

    public function getTrackDomStream()
    {
        return $this->owa_track_domstream;
    }
}

?>
