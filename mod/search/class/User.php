<?php

/**
 * User instructions
 *
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @version $Id$
 */

Core\Core::requireConfig('search');
class Search_User {

    public static function main()
    {
        if (!isset($_GET['user'])) {
            Core\Core::errorPage('404');
        }

        $command = $_GET['user'];

        switch ($command) {
            case 'search':
                Search_User::searchPost();
                break;

            default:
                Core\Core::errorPage('404');
                break;
        }
    }

    public static function searchBox()
    {
        if (SEARCH_DEFAULT) {
            $onclick = sprintf('onclick="if(this.value == \'%s\')this.value = \'\';"',
            SEARCH_DEFAULT);
        }

        
        $form = new Core\Form('search_box');
        $form->setMethod('get');
        $form->addHidden('module', 'search');
        $form->addHidden('user', 'search');
        $form->addText('search', SEARCH_DEFAULT);
        $form->setLabel('search', dgettext('search', 'Search'));

        if (Core\Settings::get('search', 'show_alternates')) {
            Search_User::addAlternates($form);
        }

        if (isset($onclick)) {
            $form->setExtra('search', $onclick);
        }
        $form->addSubmit('go', dgettext('search', 'Search'));

        $mod_list = Search_User::getModList();

        $form->addSelect('mod_title', $mod_list);

        $key = Core\Key::getCurrent();

        if (!empty($key) && !$key->isDummy()) {
            $form->setMatch('mod_title', $key->module);
        } elseif (isset($_REQUEST['mod_title'])) {
            $form->setMatch('mod_title', $_REQUEST['mod_title']);
        }

        $template = $form->getTemplate();

        $content = Core\Template::process($template, 'search', 'search_box.tpl');
        Layout::add($content, 'search', 'search_box');
    }

    public static function getModList()
    {
        $db = new Core\DB('search');
        $db->addColumn('module', null, null, false, true);
        $db->addColumn('modules.proper_name');
        $db->addGroupBy('modules.proper_name');
        $db->addWhere('search.module', 'modules.title');
        $db->setIndexBy('module');
        $result = $db->select('col');

        if (Core\Error::isError($result)) {
            Core\Error::log($result);
            $result = NULL;
        }

        $mod_list = array('all'=> dgettext('search', 'All modules'));
        if (!empty($result)) {
            $mod_list = array_merge($mod_list, $result);
        }

        return $mod_list;
    }

    public function sendToAlternate($alternate, $search_phrase)
    {
        $file = Core\Core::getConfigFile('search', 'alternate.php');
        if (!$file) {
            Core\Core::errorPage();
            exit();
        }

        include($file);

        if (!isset($alternate_search_engine) || !is_array($alternate_search_engine) ||
        !isset($alternate_search_engine[$alternate])) {
            Core\Core::errorPage();
            exit();
        }

        $gosite = &$alternate_search_engine[$alternate];

        $query_string = str_replace(' ', '+', $search_phrase);

        $site = urlencode(Core\Core::getHomeHttp(FALSE, FALSE, FALSE));
        $url = sprintf($gosite['url'], $query_string, $site);

        header('location: ' . $url);
        exit();
    }

    public static function searchPost()
    {
        $search_phrase = @$_GET['search'];
        $search_phrase = str_replace('+', ' ', $search_phrase);
        $search_phrase = Search::filterWords($search_phrase);

        if (isset($_GET['alternate']) && $_GET['alternate'] != 'local') {
            Search_User::sendToAlternate($_GET['alternate'], $search_phrase);
            exit();
        }

        $form = new Core\Form('search_box');
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

        if (Core\Error::isError($result)) {
            Core\Error::log($result);
            $template['SEARCH_RESULTS'] = dgettext('search', 'A problem occurred during your search.');
        } elseif (empty($result)) {
            $template['SEARCH_RESULTS'] = dgettext('search', 'No results found.');
        } else {
            $template['SEARCH_RESULTS'] = $result;
        }

        $template['SEARCH_RESULTS_LABEL'] = dgettext('search', 'Search Results');

        $content = Core\Template::process($template, 'search', 'search_page.tpl');

        Layout::add($content);
    }

    public static function addAlternates(Core\Form $form)
    {
        $file = Core\Core::getConfigFile('search', 'alternate.php');
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

    public static function getIgnore()
    {
        $db = new Core\DB('search_stats');
        $db->addWhere('ignored', 1);
        $db->addColumn('keyword');
        return $db->select('col');
    }

    public static function getResults($phrase, $module=NULL, $exact_match=FALSE)
    {
        Core\Core::initModClass('search', 'Stats.php');

        $pageTags = array();
        $pageTags['MODULE_LABEL'] = dgettext('search', 'Module');
        $pageTags['TITLE_LABEL']    = dgettext('search', 'Title');

        $ignore = Search_User::getIgnore();
        if (Core\Error::isError($ignore)) {
            Core\Error::log($ignore);
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

                $pager = new Core\DBPager('phpws_key', 'Key');
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
            Core\Key::restrictView($pager->db, $module);
        } else {
            Core\Key::restrictView($pager->db);
        }

        $result = $pager->get();
        Search_Stats::record($words, $pager->total_rows, $exact_match);

        return $result;
    }


}

?>