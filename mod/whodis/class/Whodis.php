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

    function purge()
    {
        $db = new PHPWS_DB('whodis');
        $go = false;
        if (!empty($_POST['days_old'])) {
            $days = (int)$_POST['days_old'];
            $updated = mktime() - (86400 * $days);
            $db->addWhere('updated', $updated, '<', null, 1);
            $go = true;
        }

        if (!empty($_POST['visit_limit'])) {
            $db->addWhere('visits', (int)$_POST['visit_limit'], '<=', 'and', 1);
            $go = true;
        }

        if (isset($_POST['delete_checked']) && !empty($_POST['referrer'])) {
            if(is_array($_POST['referrer'])) {
                $db->addWhere('id', $_POST['referrer'], 'in', 'or', 2);
                $db->setGroupConj(2, 'or');
            }
            $go = true;
        }

        if (!$go) {
            return false;
        }

        return $db->delete();
    }

    function admin()
    {
        translate('whodis');
        if (isset($_POST['op'])) {
            switch ($_POST['op']) {
            case 'purge':
                Whodis::purge();
                PHPWS_Core::goBack();
                break;
            }
        }

        PHPWS_Core::initCoreClass('DBPager.php');
        PHPWS_Core::initModClass('whodis', 'Whodis_Referrer.php');

        $form = new PHPWS_Form('purge');
        $form->addHidden('module', 'whodis');
        $form->addHidden('op', 'purge');
        $days = array(0     => _('- Referrer age -'),
                      1     => _('1 day old'),
                      3     => _('3 days old'),
                      7     => _('1 week old'),
                      14    => _('2 weeks old'),
                      30    => _('1 month old'),
                      90    => _('3 months old'),
                      365   => _('1 year old'),
                      'all' => _('Everything'));

        $form->addSelect('days_old', $days);

        $form->addText('visit_limit');
        $form->setSize('visit_limit', 4, 4);
        $form->addSubmit(_('Purge'));
        $form->setLabel('visit_limit', _('Visits'));
        $form->addSubmit('delete_checked', _('Delete checked'));

        $page_tags = $form->getTemplate();

        $page_tags['CHECK_ALL'] = javascript('check_all', array('checkbox_name'=>'referrer[]'));

        $pager = new DBPager('whodis', 'Whodis_Referrer');
        $pager->setModule('whodis');
        $pager->setTemplate('admin.tpl');
        $pager->setSearch('url');

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
        translate();
        Layout::add(PHPWS_Controlpanel::display($content));
    }

}
?>