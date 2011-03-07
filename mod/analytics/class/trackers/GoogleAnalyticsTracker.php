<?php

/**
 * Google Analytics implementation of Tracker
 * @author Jeff Tickle <jtickle at tux dot appstate dot edu>
 */

PHPWS_Core::initModClass('analytics', 'Tracker.php');

class GoogleAnalyticsTracker extends Tracker
{
    public function track()
    {
        $vars['TRACKER_ID'] = $this->getAccount();
        $code = PHPWS_Template::process($vars, 'analytics', 'GoogleAnalytics.tpl');

        self::addEndBody($code);
    }

    public function trackerType()
    {
        return 'GoogleAnalyticsTracker';
    }
}

?>
