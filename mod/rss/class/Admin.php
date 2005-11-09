<?php

  /**
   * @author Matthew McNaney <mcnaney at gmail dot com>
   * @version $Id$
   */

class RSS_Admin {

    function main()
    {

        $command = $_REQUEST['command'];

        switch ($command) {
        case 'admin':
            $tpl = RSS_Admin::admin();
            break;
        }
        
        PHPWS_Template::process($tpl, 'rss', 'main.tpl');

    }


    function admin()
    {
        $tpl['TITLE'] = _('Administrate RSS Feeds');
        $db = & new PHPWS_DB('modules');
        $db->setDistinct(TRUE);
        $db->addWhere('title', 'phpws_key.module');
        $db->addWhere('phpws_key.url', null, 'is not');

        $result = $db->select();

        if (empty($result)) {
            $tpl['CONTENT'] = _('No keys available for feeds. Come back after you have created some content.');
            return $tpl;
        }

    }
}

?>