<?php

  /**
   * User instructions
   * 
   * @author Matthew McNaney <mcnaney at gmail dot com>
   * @version $Id$
   */

class Search_User {

    function main()
    {
        if (!isset($_REQUEST['user'])) {
            PHPWS_Core::errorPage('404');
        }

        $command = $_REQUEST['user'];

        switch ($command) {
        case 'search':
            if (empty($_REQUEST['search'])) {
                PHPWS_Core::goBack();
            }
            Search_User::searchPost();
            break;

        default:
            PHPWS_Core::errorPage('404');
            break;
        }
    }

    function searchBox()
    {
        //        PHPWS_Core::requireConfig('search');

        $form = & new PHPWS_Form('search_box');
        $form->setMethod('get');
        $form->addHidden('module', 'search');
        $form->addHidden('user', 'search');
        $form->addText('search');
        $form->addSubmit('go', _('Search'));

        $result = Key::modulesInUse();

        if (PEAR::isError($result)) {
            PHPWS_Error::log($result);
            $result = NULL;
        }


        $mod_list = array('all'=> _('All modules'));

        if (!empty($result)) {
            $mod_list = array_merge($mod_list, $result);
        }

        $form->addSelect('mod_title', $mod_list);
        
        $key = Key::getCurrent();

        if (!empty($key) && !$key->isHomeKey()) {
            $form->setMatch('mod_title', $key->module);
        } elseif (isset($_REQUEST['mod_title'])) {
            $form->setMatch('mod_title', $_REQUEST['mod_title']);
        }


        $template = $form->getTemplate();
        
        $content = PHPWS_Template::process($template, 'search', 'search_box.tpl');
        Layout::add($content, 'search', 'search_box');
    }

    function searchPost()
    {
        $search_phrase = strip_tags($_REQUEST['search']);
        if (isset($_REQUEST['mod_title']) && $_REQUEST['mod_title'] != 'all') {
            $module = preg_replace('/\W/', '', $_REQUEST['mod_title']);
        } else {
            $module = NULL;
        }

        $result = Search_User::getResults($search_phrase, $module);

        if (PEAR::isError($result)) {
            PHPWS_Error::log($result);
            $template['SEARCH_RESULTS'] = _('A problem occurred during your search.');
        } elseif (empty($result)) {
            $template['SEARCH_RESULTS'] = _('No results found.');
        } else {
            $template['SEARCH_RESULTS'] = $result;
        }

        $template['SEARCH_RESULTS_LABEL'] = _('Search Results');

        $content = PHPWS_Template::process($template, 'search', 'search_page.tpl');

        Layout::add($content);
    }

    function getResults($phrase, $module=NULL)
    {
        PHPWS_Core::requireConfig('search');

        $pageTags = array();
        $pageTags['MODULE_LABEL'] = _('Module');
        $pageTags['URL_LABEL']    = _('Url');


        PHPWS_Core::initCoreClass('DBPager.php');
        $pager = & new DBPager('phpws_key', 'Key');
        $pager->setModule('search');
        $pager->setTemplate('search_results.tpl');
        $pager->addToggle('class="bgcolor1"');
        $pager->addRowTags('getTplTags');
        $pager->addPageTags($pageTags);

        if (empty($phrase)) {
            return FALSE;
        }
        $words = explode(' ', $phrase);

        if (empty($words)) {
            return FALSE;
        }

        if ($module) {
            $pager->addWhere('phpws_key.module', $module);
        }

        $pager->addWhere('search.key_id', 'phpws_key.id');
        $pager->addWhere('active', 1);
        $pager->addWhere('view_permission', 'null');

        if (!Current_User::isLogged()) {
            $pager->addWhere('restricted', 0);
        }

        foreach ($words as $keyword) {
            if (strlen($keyword) < SEARCH_MIN_WORD_LENGTH) {
                continue;
            }

            $pager->addWhere('search.keywords', "%$keyword%", 'like', 'or', 1);
        }
        $pager->db->setGroupConj(1, 'AND');
        return $pager->get(FALSE);
    }


}

?>