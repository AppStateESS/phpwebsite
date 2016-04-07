<?php

/**
 * Description
 * @author Jeff Tickle <jtickle at tux dot appstate dot edu>
 */

require_once(PHPWS_SOURCE_DIR . 'mod/analytics/class/TrackerFactory.php');

class TrackerFactoryTest extends PHPUnit_Framework_TestCase
{
    public function testInstantiateTrackers()
    {
        $this->assertTrue(
            TrackerFactory::newByType('GoogleAnalyticsTracker')
            instanceof GoogleAnalyticsTracker);
        $this->assertTrue(
            TrackerFactory::newByType('OpenWebAnalyticsTracker')
            instanceof OpenWebAnalyticsTracker);
        $this->assertTrue(
            TrackerFactory::newByType('PiwikTracker')
            instanceof PiwikTracker);
    }
}

