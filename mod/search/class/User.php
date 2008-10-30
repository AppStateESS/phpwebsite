<?php

  /**
   * User instructions
   *
   * @author Matthew McNaney <mcnaney at gmail dot com>
   * @version $Id$
   */

PHPWS_Core::requireConfig('search');
class Search_User {

    function main()
    {
        if (!isset($_GET['user'])) {
            PHPWS_Core::errorPage('404');
        }

        $command = $_GET['user'];

        switch ($command) {
        case 'search':
            Search_User::searchPost();
            break;

        default:
            PHPWS_Core::errorPage('404');
            break;
        }
    }

    function searchBox()
    {
        if (SEARCH_DEFAULT) {
            $onclick = sprintf('onclick="if(this.value == \'%s\')this.value = \'\';"',
                               SEARCH_DEFAULT);
        }

        PHPWS_Core::initCoreClass('Form.php');

        $form = new PHPWS_Form('search_box');
        $form->setMethod('get');
        $form->addHidden('module', 'search');
        $form->addHidden('user', 'search');
        $form->addText('search', SEARCH_DEFAULT);
        $form->setLabel('search', dgettext('search', 'Search'));

        if (PHPWS_Settings::get('search', 'show_alternates')) {
            Search_User::addAlternates($form);
        }

        if (isset($onclick)) {
            $form->setExtra('search', $onclick);
        }
        $form->addSubmit('go', dgettext('search', 'Search'));

        $mod_list = Search_User::getModList();

        $form->addSelect('mod_title', $mod_list);

        $key = Key::getCurrent();

        if (!empty($key) && !$key->isDummy()) {
            $form->setMatch('mod_title', $key->module);
        } elseif (isset($_REQUEST['mod_title'])) {
            $form->setMatch('mod_title', $_REQUEST['mod_title']);
        }

        $template = $form->getTemplate();

        $content = PHPWS_Template::process($template, 'search', 'search_box.tpl');
        Layout::add($content, 'search', 'search_box');
    }

    function getModList()
    {
        $db = new PHPWS_DB('search');
        $db->addColumn('module', null, null, false, true);
        $db->addColumn('modules.proper_name');
        $db->addGroupBy('modules.proper_name');
        $db->addWhere('search.module', 'modules.title');
        $db->setIndexBy('module');
        $result = $db->select('col');

        if (PEAR::isError($result)) {
            PHPWS_Error::log($result);
            $result = NULL;
        }

        $mod_list = array('all'=> dgettext('search', 'All modules'));
        if (!empty($result)) {
            $mod_list = array_merge($mod_list, $result);
        }

        return $mod_list;
    }

    function sendToAlternate($alternate, $search_phrase)
    {
        $file = PHPWS_Core::getConfigFile('search', 'alternate.php');
        if (!$file) {
            PHPWS_Core::errorPage();
            exit();
        }

        include($file);

        if (!isset($alternate_search_engine) || !is_array($alternate_search_engine) ||
            !isset($alternate_search_engine[$alternate])) {
            PHPWS_Core::errorPage();
            exit();
        }

        $gosite = &$alternate_search_engine[$alternate];

        $query_string = str_replace(' ', '+', $search_phrase);

        $site = urlencode(PHPWS_Core::getHomeHttp(FALSE, FALSE, FALSE));
        $url = sprintf($gosite['url'], $query_string, $site);

        header('location: ' . $url);
        exit();
    }

    function searchPost()
    {
        $search_phrase = @$_GET['search'];
        $search_phrase = str_replace('+', ' ', $search_phrase);
        $search_phrase = Search::filterWords($search_phrase);

        if (isset($_GET['alternate']) && $_GET['alternate'] != 'local') {
            Search_User::sendToAlternate($_GET['alternate'], $search_phrase);
            exit();
        }

        $form = new PHPWS_Form('search_box');
        $form->setMethod('get');
        $form->addHidden('module', 'search');
        $form->addHidden('user', 'search');
        $form->addSubmit(dgettext('search', 'Search'));
        $form->addText('search', $search_phrase);
        $form->setSize('search', 40);
        $form->setLabel('search', dgettext('search', 'Search for:'));

        $form->addCheck('exact_only', 1);
        $form->setLabel('exact_only', dgettext('search', 'Exact matches only'));
        if (isset($_GET['exact_only'])) {
            $exact_match = TRUE;
            $form->setMatch('exact_only', 1);
        } else {
            $exact_match = FALSE;
        }

        $mod_list = Search_User::getModList();
        $form->addSelect('mod_title', $mod_list);
        $form->setLabel('mod_title', dgettext('search', 'Module list'));
        if (isset($_GET['mod_title'])) {
            $form->setMatch('mod_title', $_GET['mod_title']);
        }

        Search_User::addAlternates($form);

        $template = $form->getTemplate();

        if (isset($_GET['mod_title']) && $_GET['mod_title'] != 'all') {
            $module = preg_replace('/\W/', '', $_GET['mod_title']);
        } else {
            $module = NULL;
        }

        $template['SEARCH_LOCATION'] = dgettext('search', 'Search location');
        $template['ADVANCED_LABEL'] = dgettext('search', 'Advanced Search');

        $result = Search_User::getResults($search_phrase, $module, $exact_match);

        if (PEAR::isError($result)) {
            PHPWS_Error::log($result);
            $template['SEARCH_RESULTS'] = dgettext('search', 'A problem occurred during your search.');
        } elseif (empty($result)) {
            $template['SEARCH_RESULTS'] = dgettext('search', 'No results found.');
        } else {
            $template['SEARCH_RESULTS'] = $result;
        }

        $template['SEARCH_RESULTS_LABEL'] = dgettext('search', 'Search Results');

        $content = PHPWS_Template::process($template, 'search', 'search_page.tpl');

        Layout::add($content);
    }

    function addAlternates(PHPWS_Form $form)
    {
        $file = PHPWS_Core::getConfigFile('search', 'alternate.php');
        if ($file) {
            include($file);

            if (!empty($alternate_search_engine) && is_array($alternate_search_engine)) {
                $alternate_sites['local'] = dgettext('search', 'Local');
                foreach ($alternate_search_engine as $title=>$altSite) {
                    $alternate_sites[$title] = $altSite['title'];
                }

                $form->addRadio('alternate', array_keys($alternate_sites));
                $form->setLabel('alternate', $alternate_sites);
                $form->setMatch('alternate', 'local');
            }
        }
    }

    function getIgnore()
    {
        $db = new PHPWS_DB('search_stats');
        $db->addWhere('ignored', 1);
        $db->addColumn('keyword');
        return $db->select('col');
    }

    function getResults($phrase, $module=NULL, $exact_match=FALSE)
    {
        PHPWS_Core::initModClass('search', 'Stats.php');

        $pageTags = array();
        $pageTags['MODULE_LABEL'] = dgettext('search', 'Module');
        $pageTags['TITLE_LABEL']    = dgettext('search', 'Title');

        $ignore = Search_User::getIgnore();
        if (PEAR::isError($ignore)) {
            PHPWS_Error::log($ignore);
            $ignore = NULL;
        }

        if (empty($phrase)) {
            return FALSE;
        }

        $words = explode(' ', $phrase);

        if (!empty($ignore)) {
            $words_removed = array_intersect($words, $ignore);

            if (!empty($words_removed)) {
                $pageTags['REMOVED_LABEL'] = dgettext('search', 'The following search words were ignored');
                $pageTags['IGNORED_WORDS'] = implode(' ', $words_removed);
                foreach ($words_removed as $remove) {
                    $key = array_search($remove, $words);
                    unset($words[$key]);
                }
            }
        }

        if (empty($words)) {
            return FALSE;
        }

        PHPWS_Core::initCoreClass('DBPager.php');
        $pager = new DBPager('phpws_key', 'Key');
        $pager->setModule('search');
        $pager->setTemplate('search_results.tpl');
        $pager->addToggle('class="bgcolor1"');
        $pager->addRowTags('getTplTags');
        $pager->addPageTags($pageTags);

        foreach ($words as $keyword) {
            if (strlen($keyword) < SEARCH_MIN_WORD_LENGTH) {
                continue;
            }

            if ($exact_match) {
                $s_keyword = "%$keyword %";
            } else {
                $s_keyword = "%$keyword%";
            }

            $pager->addWhere('search.keywords', $s_keyword, 'like', 'or', 1);
        }

        // No keywords were set. All under minimum word length
        if (empty($s_keyword)) {
            return null;
        }

        $pager->setEmptyMessage(dgettext('search', 'Nothing found'));
        $pager->db->setGroupConj(1, 'AND');

        if ($module) {
            $pager->addWhere('search.module', $module);
            Key::restrictView($pager->db, $module);
        } else {
            Key::restrictView($pager->db);
        }

        $result = $pager->get();
        Search_Stats::record($words, $pager->total_rows, $exact_match);

        return $result;
    }


}

?>