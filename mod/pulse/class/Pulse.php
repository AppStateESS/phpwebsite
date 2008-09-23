<?php
/**
 * @version $Id$
 * @author Matthew McNaney <mcnaney at gmail dot com>
 */

class Pulse {
    public $title   = null;
    public $content = null;
    public $message = null;
    public $panel   = null;

    public function ping()
    {
        PHPWS_Core::initModClass('pulse', 'Pulse_Schedules.php');
        $db = new PHPWS_DB('pulse_schedule');
        $db->addWhere('pulse_time', mktime(), '<');

        $result = $db->getObjects('Pulse_Schedule');
        if (PHPWS_Error::logIfError($result) || empty($result)) {
            return;
        }

        foreach ($result as $pulse_sch) {
            $pulse_sch->commit();
        }
    }

    public function admin()
    {
        $this->loadPanel();

        if (isset($_REQUEST['aop'])) {
            $command = & $_REQUEST['aop'];
        } elseif (isset($_REQUEST['tab'])) {
            $command = & $_REQUEST['tab'];
        } else {
            $command = 'main';
        }

        switch ($command) {
        case 'main':
            $this->listSchedules();
            break;
        }

        Layout::add(PHPWS_ControlPanel::display($this->panel->display($this->content, $this->title, $this->message)));
    }

    public function loadPanel()
    {
        PHPWS_Core::initModClass('controlpanel', 'Panel.php');
        $link = 'index.php?module=pulse&amp;aop=main';
        $tabs['main'] = array('title'=>dgettext('pulse', 'Schedules'),
                              'link'=>$link);

        $tabs['settings'] = array('title'=>dgettext('pulse', 'Settings'),
                                  'link'=>$link);


        $this->panel = new PHPWS_Panel('pulse');
        $this->panel->quickSetTabs($tabs);
    }

    public function listSchedules()
    {
        PHPWS_Core::initCoreClass('DBPager.php');
        PHPWS_Core::initModClass('pulse', 'Pulse_Schedules.php');
        $pager = new DBPager('pulse_schedule', 'Pulse_Schedule');
        $pager->addSortHeader('module', dgettext('pulse', 'Module'));
        $pager->addSortHeader('pulse_type', dgettext('pulse', 'Type'), dgettext('pulse', 'Pulse type'));
        $pager->addSortHeader('pulse_time', dgettext('pulse', 'Next'), dgettext('pulse', 'Next pulse time'));
        $pager->addSortHeader('last_run', dgettext('pulse', 'Last'), dgettext('pulse', 'Last pulse time'));
        $pager->addSortHeader('repeats', dgettext('pulse', 'Repeats'), dgettext('pulse', 'Number of repeats'));
        $pager->addSortHeader('completed', dgettext('pulse', 'Completed'), dgettext('pulse', 'Pulses completed'));
        $pager->addSortHeader('active', dgettext('pulse', 'Active'));
        $pager->setModule('pulse');
        $pager->setTemplate('list.tpl');
        $pager->addRowTags('row_tags');

        $this->content = $pager->get();
        $this->title = dgettext('pulse', 'Pulse Schedules');
    }
}

?>