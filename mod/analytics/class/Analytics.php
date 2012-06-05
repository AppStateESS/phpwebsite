<?php

/**
 * Analytics Controller Class
 * @author Jeff Tickle <jtickle at tux dot appstate dot edu>
 */

class Analytics
{
    public static function injectTrackers()
    {
        PHPWS_Core::initModClass('analytics', 'TrackerFactory.php');
        $trackers = TrackerFactory::getActive();

        if(empty($trackers)) return;

        foreach($trackers as $tracker) {
            $tracker->track();
        }
    }

    public static function process()
    {
        if(!Current_User::authorized('analytics')) Current_User::disallow();

        $panel = self::cpanel();

        if(isset($_REQUEST['command'])) {
            $command = $_REQUEST['command'];
        } else {
            $command = $panel->getCurrentTab();
        }

        switch($command) {
        case 'list':
            $panel->setContent(self::listTrackers());
            break;

        case 'new':
            $panel->setContent(self::newTracker());
            break;

        case 'create':
            $panel->setContent(self::createTracker());
            break;

        case 'edit':
            $panel->setContent(self::editTracker());
            break;

        case 'delete':
            $panel->setContent(self::deleteTracker());
            break;

        case 'save_tracker':
            $panel->setContent(self::saveTracker());
            break;
        }

        Layout::add(PHPWS_ControlPanel::display($panel->display()));
    }

    public static function listTrackers()
    {
        PHPWS_Core::initModClass('analytics', 'GenericTracker.php');
        PHPWS_Core::initCoreClass('DBPager.php');

        $pager = new DBPager('analytics_tracker', 'GenericTracker');
        $pager->addSortHeader('name', dgettext('analytics', 'Name'));
        $pager->addSortHeader('type', dgettext('analytics', 'Type'));
        $pager->addSortHeader('active', dgettext('analytics', 'Active'));

        $pageTags = array();
        $pageTags['ACTION'] = dgettext('analytics', 'Action');
        $pageTags['ACCOUNT'] = dgettext('analytics', 'Account ID');

        $pager->setModule('analytics');
        $pager->setTemplate('list.tpl');
        $pager->addToggle('class="toggle1"');
        $pager->addRowTags('getPagerTags');
        $pager->addPageTags($pageTags);
        $pager->setSearch('name');
        $pager->setDefaultOrder('name', 'asc');
        $pager->cacheQueries();

        return $pager->get();
    }

    public static function newTracker()
    {
        $form = new PHPWS_Form('tracker');
        $form->addHidden('module', 'analytics');
        $form->addHidden('command', 'create');
        $form->addSubmit('submit', dgettext('analytics', 'Next'));

        $classes = TrackerFactory::getAvailableClasses();
        $trackers = array();
        foreach($classes as $class) {
            $trackers[$class] = $class;
        }
        $form->addSelect('tracker', $trackers);
        $form->setLabel('tracker', dgettext('analytics', 'Tracker'));
        $form->setRequired('tracker');

        $tpl = $form->getTemplate();
        return PHPWS_Template::process($tpl, 'analytics', 'select.tpl');
    }

    public static function createTracker()
    {
        $tracker = TrackerFactory::newByType($_REQUEST['tracker']);
        return self::showEditForm($tracker);
    }

    public static function editTracker()
    {
        PHPWS_Core::initModClass('analytics', 'TrackerFactory.php');
        $tracker = TrackerFactory::getById($_REQUEST['tracker_id']);
        return self::showEditForm($tracker);
    }

    public static function deleteTracker()
    {
        PHPWS_Core::initModClass('analytics', 'TrackerFactory.php');
        $tracker = TrackerFactory::getById($_REQUEST['tracker_id']);
        $tracker->delete();

        self::redirectList();
    }

    public static function saveTracker()
    {
        PHPWS_Core::initModClass('analytics', 'TrackerFactory.php');
        if(isset($_REQUEST['tracker_id'])) {
            $tracker = TrackerFactory::getById($_REQUEST['tracker_id']);
        } else {
            $tracker = TrackerFactory::newByType($_REQUEST['tracker']);
        }

        $tracker->processForm($_REQUEST);
        $tracker->save();

        self::redirectList();
    }

    public static function redirectList()
    {
        $redirect = PHPWS_Text::linkAddress('analytics', array('tab'=>'list'), true, false, false);

        header('HTTP/1.1 303 See Other');
        header('Location: ' . $redirect);
        exit();
    }

    public static function showEditForm(Tracker $tracker)
    {
        $tpl = array();

        $tpl['TRACKER_TYPE'] = $tracker->trackerType();

        $form = new PHPWS_Form('tracker');
        $form->addHidden('module', 'analytics');
        $form->addHidden('command', 'save_tracker');
        $form->addSubmit('submit', dgettext('analytics', 'Save Tracker'));

        if(isset($_REQUEST['tracker'])) {
            $form->addHidden('tracker', $_REQUEST['tracker']);
        }

        if($tracker->getId() > 0) {
            $form->addHidden('tracker_id', $tracker->getId());
        }

        $form->addText('name', $tracker->getName());
        $form->setLabel('name', dgettext('analytics', 'Friendly Name'));
        $form->setRequired('name');

        $form->addCheck('active', 1);
        $form->setMatch('active', $tracker->isActive());
        $form->setLabel('active', dgettext('analytics', 'Currently Active'));

        $form->addCheck('disable_if_logged', 1);
        $form->setMatch('disable_if_logged', $tracker->getDisableIfLogged());
        $form->setLabel('disable_if_logged', dgettext('analytics', 'Disable Analytics if a user is logged in'));

        $tracker->addForm($form);

        $tpl = array_merge($tpl, $form->getTemplate());

        $tpl['TRACKER_FORM'] = PHPWS_Template::process($tpl, 'analytics', $tracker->getFormTemplate());

        return PHPWS_Template::process($tpl, 'analytics', 'edit.tpl');
    }

    public static function cpanel()
    {
        PHPWS_Core::initModClass('controlpanel', 'Panel.php');
        
        $link = PHPWS_Text::linkAddress('analytics', null, false, false, true, false);

        $tabs['list'] = array('title' => dgettext('analytics', 'List Trackers'), 'link' => $link);
        $tabs['new']  = array('title' => dgettext('analytics', 'New Tracker'),   'link' => $link);

        $panel = new PHPWS_Panel('analyticsPanel');
        $panel->enableSecure();
        $panel->quickSetTabs($tabs);
        $panel->setModule('analytics');
        $panel->setPanel('panel.tpl');

        return $panel;
    }
}

?>
