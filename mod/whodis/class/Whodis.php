<?php
  /**
   * @version $Id$
   * @author Matthew McNaney <mcnaney at appstate dot edu>
   */

class Whodis {

    function record()
    {
        if (!empty($_SERVER['HTTP_REFERER'])) {
            $referrer = & $_SERVER['HTTP_REFERER'];
            $home_url = PHPWS_Core::getHomeHttp();
            $preg_match = str_replace('/', '\/', ($home_url));

            if (preg_match('/^' . $preg_match . '/', $referrer)) {
                return;
            }

            PHPWS_Core::initModClass('whodis', 'Whodis_Referrer.php');

            $whodis = new Whodis_Referrer;
            $result = $whodis->save($referrer);
            if (PEAR::isError($result)) {
                PHPWS_Error::log($result);
            }
        }
    }

    function admin()
    {
        PHPWS_Core::initCoreClass('DBPager.php');
        PHPWS_Core::initModClass('whodis', 'Whodis_Referrer.php');

        $pager = new DBPager('whodis', 'Whodis_Referrer');
        $pager->setModule('whodis');
        $pager->setTemplate('admin.tpl');

        $page_tags['URL_LABEL']     = _('Referrer');
        $page_tags['CREATED_LABEL'] = _('First visit');
        $page_tags['UPDATED_LABEL'] = _('Last visit');
        $page_tags['VISITS_LABEL']  = _('Total visits');

        $limits[4]  = 10;
        $limits[9]  = 25;
        $limits[16] = 50;
        $pager->setLimitList($limits);
	$pager->setDefaultLimit(25);

        $pager->addPageTags($page_tags);
        $pager->addRowTags('getTags');
        $pager->setOrder('updated', 'desc', true);
        $content = $pager->get();

        Layout::add(PHPWS_Controlpanel::display($content));
    }

}
?>