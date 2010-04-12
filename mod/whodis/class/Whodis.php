<?php
/**
 * @version $Id$
 * @author Matthew McNaney <mcnaney at appstate dot edu>
 */

class Whodis {

    public static function record()
    {
        if (!empty($_SERVER['HTTP_REFERER'])) {
            $referrer = & $_SERVER['HTTP_REFERER'];
            if (!preg_match('/["\']/', $referrer)) {
                if (Whodis::passFilters($referrer)) {
                    PHPWS_Core::initModClass('whodis', 'Whodis_Referrer.php');

                    $whodis = new Whodis_Referrer;
                    $result = $whodis->save($referrer);
                    if (PHPWS_Error::isError($result)) {
                        PHPWS_Error::log($result);
                    }
                }
            }
        }
    }

    public static function passFilters($referrer)
    {
        $home_url = PHPWS_Core::getHomeHttp();
        $preg_match = str_replace('/', '\/', ($home_url));

        if (preg_match('/^' . $preg_match . '/', $referrer)) {
            return false;
        }

        $db = new PHPWS_DB('whodis_filters');
        $db->addColumn('filter');
        $filters = $db->select('col');

        if (empty($filters)) {
            return true;
        } elseif (PHPWS_Error::isError($filters)) {
            PHPWS_Error::log($filters);
            return true;
        }

        foreach ($filters as $flt) {
            if (preg_match("/$flt/", $referrer)) {
                return false;
            }
        }
        return true;
    }

    public function purge()
    {
        $db = new PHPWS_DB('whodis');
        $go = false;
        if (!empty($_POST['days_old'])) {
            $days = (int)$_POST['days_old'];
            $updated = time() - (86400 * $days);
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

    public static function admin()
    {
        if (!Current_User::allow('whodis')) {
            Current_User::disallow();
        }


        if (isset($_REQUEST['op'])) {
            switch ($_REQUEST['op']) {
                case 'purge':
                    Whodis::purge();
                    PHPWS_Core::goBack();
                    break;

                case 'filters':
                    Whodis::filters();
                    break;

                case 'filters_option':
                    Whodis::filterOption();
                    PHPWS_Core::goBack();
                    break;

                case 'list':
                default:
                    Whodis::listReferrers();
            }
        } else {
            Whodis::listReferrers();
        }
    }

    public function filterOption()
    {
        if (isset($_POST['add_filter_button']) && !empty($_POST['add_filter'])) {
            $filter = preg_replace('/[^\w\.-\s]/', '', strip_tags($_POST['add_filter']));
            if (!empty($filter)) {
                $db = new PHPWS_DB('whodis_filters');
                $db->addValue('filter', $filter);
                $result = $db->insert();
                if (PHPWS_Error::isError($result)) {
                    PHPWS_Error::log($result);
                }
            }
        } elseif (isset($_POST['delete_checked'])) {
            if (!empty($_POST['filter_pick']) && is_array($_POST['filter_pick'])) {
                $db = new PHPWS_DB('whodis_filters');
                $db->addWhere('id', $_POST['filter_pick']);
                $result = $db->delete();
                if (PHPWS_Error::isError($result)) {
                    PHPWS_Error::log($result);
                }
            }
        }
    }

    public function filters()
    {
        PHPWS_Core::initCoreClass('DBPager.php');

        $form = new PHPWS_Form('filter');
        $form->addHidden('module', 'whodis');
        $form->addHidden('op', 'filters_option');
        $form->addText('add_filter');
        $form->setSize('add_filter', 30, 60);
        $form->addSubmit('add_filter_button', dgettext('whodis', 'Add filter'));
        $form->addSubmit('delete_checked', dgettext('whodis', 'Delete checked'));

        $page_tags = $form->getTemplate();

        $page_tags['CHECK_ALL'] = javascript('check_all', array('checkbox_name'=>'filter_pick[]'));
        $pager = new DBPager('whodis_filters');
        $pager->setModule('whodis');
        $pager->setTemplate('filter.tpl');
        $pager->setSearch('filter');

        $vars['op'] = 'list';
        $links[] = PHPWS_Text::moduleLink(dgettext('whodis', 'Referrers'), 'whodis', $vars);

        $vars['op'] = 'filters';
        $links[] = PHPWS_Text::moduleLink(dgettext('whodis', 'Filters'), 'whodis', $vars);

        $page_tags['ADMIN_LINKS']  = implode(' | ', $links);
        $page_tags['FILTER_LABEL'] = dgettext('whodis', 'Filters');

        $limits[4]  = 10;
        $limits[9]  = 25;
        $limits[16] = 50;
        $pager->setLimitList($limits);
        $pager->setDefaultLimit(25);
        $pager->addRowFunction(array('Whodis', 'checkbox'));

        $pager->addPageTags($page_tags);
        $pager->setDefaultOrder('filter');
        $content = $pager->get();

        Layout::add(PHPWS_Controlpanel::display($content));

    }

    public function checkbox($values)
    {
        return array('FILTER_PICK' => sprintf('<input type="checkbox" name="filter_pick[]" value="%s" />', $values['id']));
    }

    public static function listReferrers()
    {
        PHPWS_Core::initCoreClass('DBPager.php');
        PHPWS_Core::initModClass('whodis', 'Whodis_Referrer.php');

        $form = new PHPWS_Form('purge');
        $form->addHidden('module', 'whodis');
        $form->addHidden('op', 'purge');
        $days = array(0     => dgettext('whodis', '- Referrer age -'),
        1     => dgettext('whodis', '1 day old'),
        3     => dgettext('whodis', '3 days old'),
        7     => dgettext('whodis', '1 week old'),
        14    => dgettext('whodis', '2 weeks old'),
        30    => dgettext('whodis', '1 month old'),
        90    => dgettext('whodis', '3 months old'),
        365   => dgettext('whodis', '1 year old'),
                      'all' => dgettext('whodis', 'Everything'));

        $form->addSelect('days_old', $days);

        $form->addText('visit_limit');
        $form->setSize('visit_limit', 4, 4);
        $form->addSubmit(dgettext('whodis', 'Purge'));
        $form->setLabel('visit_limit', dgettext('whodis', 'Visits'));
        $form->addSubmit('delete_checked', dgettext('whodis', 'Delete checked'));

        $page_tags = $form->getTemplate();

        $page_tags['CHECK_ALL'] = javascript('check_all', array('checkbox_name'=>'referrer[]'));

        $pager = new DBPager('whodis', 'Whodis_Referrer');
        $pager->setModule('whodis');
        $pager->setTemplate('admin.tpl');
        $pager->setSearch('url');

        $vars['op'] = 'list';
        $links[] = PHPWS_Text::moduleLink(dgettext('whodis', 'Referrers'), 'whodis', $vars);

        $vars['op'] = 'filters';
        $links[] = PHPWS_Text::moduleLink(dgettext('whodis', 'Filters'), 'whodis', $vars);
        $page_tags['ADMIN_LINKS']   = implode(' | ', $links);

        $page_tags['URL_LABEL']     = dgettext('whodis', 'Referrer');
        $page_tags['CREATED_LABEL'] = dgettext('whodis', 'First visit');
        $page_tags['UPDATED_LABEL'] = dgettext('whodis', 'Last visit');
        $page_tags['VISITS_LABEL']  = dgettext('whodis', 'Total visits');

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