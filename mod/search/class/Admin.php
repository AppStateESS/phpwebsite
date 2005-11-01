<?php

  /**
   * Allows administrator to see search results, change settings
   * etc.
   *
   * @author Matthew McNaney <mcnaney at gmail dot com>
   * @version $Id$
   */

class Search_Admin {

    function main()
    {
        if (!Current_User::allow('search')) {
            Current_User::disallow();
        }

        $panel = Search_Admin::cpanel();

        if (isset($_REQUEST['command'])) {
            $command = $_REQUEST['command'];
        } elseif (isset($_REQUEST['tab'])) {
            $command = $_REQUEST['tab'];
        } else {
            $command = $panel->getCurrentTab();
        }

        switch ($command) {
        case 'delete_keyword':
        case 'add_keyword':
        case 'remove_searchword':
            if (!Current_User::authorized('search')) {
                Current_User::disallow();
            }
            
            break;
        }
        
        switch ($command) {
        case 'keyword':
            $template = Search_Admin::keyword();
            break;

        case 'close_admin':
            unset($_SESSION['Search_Add_Words']);
            unset($_SESSION['Search_Admin']);
            PHPWS_Core::goBack();
            break;

        case 'delete_keyword':
            if (!empty($_REQUEST['keyword'])) {
                $db = & new PHPWS_DB('search_stats');
                if (is_array($_POST['keyword'])) {
                    foreach ($_POST['keyword'] as $kw) {
                        $db->addWhere('keyword', $kw);
                    }
                } else {
                    $db->addWhere('keyword', $_REQUEST['keyword']);
                }
                $result = $db->delete();
            }
            PHPWS_Core::goBack();
            break;

        case 'add_parse_word':
            if (!isset($_REQUEST['keyword'])) {
                PHPWS_Core::goBack();
            }
            Search_Admin::addParseWord($_REQUEST['keyword']);
            Search_Admin::sendMessage(_('Keywords added to admin menu.'), 'keyword');
            break;

        case 'drop_keyword':
            if (isset($_SESSION['Search_Add_Words'])) {
                $array_key = array_search($_REQUEST['kw'], $_SESSION['Search_Add_Words']);
                if ($array_key !==  FALSE) {
                    unset($_SESSION['Search_Add_Words'][$array_key]);
                }
            }
            PHPWS_Core::goBack();
            break;


        case 'add_keyword':
            if (!isset($_GET['kw']) || !isset($_GET['key_id'])) {
                PHPWS_Core::goBack();
            }

            Search_Admin::addKeyword($_GET['kw'], $_GET['key_id']);
            PHPWS_Core::goBack();
            break;

        case 'remove_searchword':
            if (!isset($_GET['kw']) || !isset($_GET['key_id'])) {
                PHPWS_Core::goBack();
            }

            Search_Admin::removeSearchword($_GET['kw'], $_GET['key_id']);
            PHPWS_Core::goBack();
            break;

        }

        $template['MESSAGE'] = Search_Admin::getMessage();

        $final = PHPWS_Template::process($template, 'search', 'main.tpl');

        $panel->setContent($final);
        $finalPanel = $panel->display();
        Layout::add(PHPWS_ControlPanel::display($finalPanel));
    }

    function removeSearchword($keyword, $key_id)
    {
        $search = & new Search((int)$key_id);
        if ($search->_error) {
            PHPWS_Error::log($search->_error);
            return;
        }

        $search->removeKeyword($keyword);
        return $search->save();
    }

    function addKeyword($keyword, $key_id)
    {
        $search = & new Search((int)$key_id);
        if ($search->_error) {
            PHPWS_Error::log($search->_error);
            return;
        }

        $search->addKeywords($keyword, FALSE);
        return $search->save();
    }

    function sendMessage($message, $command)
    {
        $_SESSION['Search_Message'] = $message;
        PHPWS_Core::reroute('index.php?module=search&command=' . $command);
    }

    function getMessage()
    {
        if (!isset($_SESSION['Search_Message'])) {
            return NULL;
        }
        $message = $_SESSION['Search_Message'];
        unset($_SESSION['Search_Message']);
        return $message;
    }

    function addParseWord($words)
    {
        if (!isset($_SESSION['Search_Add_Words'])) {
            $_SESSION['Search_Add_Words'] = $words;
        } else {
            $_SESSION['Search_Add_Words'] = array_merge($_SESSION['Search_Add_Words'], $words);
        }
        $_SESSION['Search_Add_Words'] = array_unique($_SESSION['Search_Add_Words']);
        $_SESSION['Search_Admin'] = TRUE;
    }

    function miniAdmin()
    {
        $key = Key::getCurrent();

        if (empty($key) || $key->isHomeKey() || isset($key->_error)) {
            $on_page = FALSE;
        } else {
            $on_page = TRUE;
        }

        if ($on_page) {
            $search = & new Search($key);
            if ($search->keywords) {
                foreach ($search->keywords as $keyword) {
                    $vars['key_id'] = $key->id;
                    $link['WORD'] = $vars['kw'] = $keyword;
                    $vars['command'] = 'remove_searchword';
                    $link['DROP_LINK'] = PHPWS_Text::secureLink(_('Drop'), 'search', $vars);
                    $tpl['current-words'][] = $link;
                }
            }
            $tpl['CURRENT_TITLE'] = _('Current keywords');
        }


        if (isset($_SESSION['Search_Add_Words'])) {
            foreach ($_SESSION['Search_Add_Words'] as $keyword) {
                $link = $vars = NULL;
                $link['WORD'] = $vars['kw'] = $keyword;
                $vars['command'] = 'drop_keyword';
                $link['DROP_LINK'] = PHPWS_Text::secureLink(_('Drop'), 'search', $vars);
                
                if ($on_page) {
                    if (!in_array($keyword, $search->keywords)) {
                        $vars['key_id'] = $key->id;
                        $vars['command'] = 'add_keyword';
                        $link['ADD_LINK'] = PHPWS_Text::secureLink(_('Add'), 'search', $vars);
                    }
                }
                
                $tpl['add-words'][] = $link;
            }
            $tpl['BANK_TITLE'] = _('Clipped words');
        }

        $tpl['TITLE'] = _('Search Admin');

        $tpl['CLOSE_LINK'] = PHPWS_Text::secureLink(_('Close'), 'search', array('module'=>'search', 'command'=>'close_admin'));

        $content = PHPWS_Template::process($tpl, 'search', 'mini_menu.tpl');

        Layout::add($content, 'search', 'admin_box');
    }

    function keyword()
    {
        PHPWS_Core::initCoreClass('DBPager.php');

        $tpl['TITLE'] = _('Keywords');

        PHPWS_Core::initModClass('search', 'Stats.php');

        $pager = & new DBPager('search_stats', 'Search_Stats');
        $pager->setModule('search');
        $pager->setTemplate('pager.tpl');
        $pager->addRowTags('getTplTags');

	$options['keyword'] = '';
        $options['delete_keyword'] = _('Delete');

        // if entered in search box, remove
        $options['add_ignore'] = _('Ignore');

        // remember word to add to items
        $options['add_parse_word'] = _('Clip word');

        $form = & new PHPWS_Form;
        $form->setMethod('get');
        $form->addHidden('module', 'search');
        $form->addSelect('command', $options);

        $template = $form->getTemplate();

        $js_vars['value'] = _('Go');
        $js_vars['select_id'] = 'command';
        $js_vars['command_match'] = 'delete_keyword';
        $js_vars['message'] = _('Are you sure you want to delete the checked item(s)?');

        $template['SUBMIT'] = javascript('select_confirm', $js_vars);
        
        $template['CHECK_ALL'] = javascript('check_all', array('checkbox_name' => 'keyword[]'));
        $template['KEYWORD_LABEL'] = _('Keyword');
        $template['SUCCESS_LABEL'] = _('Success');
        $template['FAILURE_LABEL'] = _('Failure');
        $template['LAST_CALL_DATE_LABEL'] = _('Last called');
        $template['HIGHEST_RESULT_LABEL'] = _('Highest result');
        $template['MIXED_LABEL'] = _('Mixed');
        $pager->addPageTags($template);
        $pager->addToggle('class="bgcolor1"');
        $pager->setSearch('keyword');
        
        $tpl['CONTENT'] = $pager->get();
        
        return $tpl;
    }
    
    
    function &cpanel()
    {
        PHPWS_Core::initModClass('controlpanel', 'Panel.php');
        $link = 'index.php?module=search';
        $tab['keyword'] = array ('title'=>_('Keywords'), 'link'=> $link);

        $panel = & new PHPWS_Panel('search');
        $panel->quickSetTabs($tab);

        $panel->setModule('search');
        return $panel;
    }

}

?>