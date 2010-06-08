<?php

/**
 * Allows administrator to see search results, change settings
 * etc.
 *
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @version $Id$
 */

class Search_Admin {

    public static function main()
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
            case 'add_ignore':
                if (!Current_User::authorized('search')) {
                    Current_User::disallow();
                }
                break;
        }

        switch ($command) {
            case 'keyword':
                $template = Search_Admin::keyword();
                break;

            case 'ignore':
                $template = Search_Admin::ignore();
                break;

            case 'settings':
                $template = Search_Admin::settings();
                break;

            case 'close_admin':
                unset($_SESSION['Search_Add_Words']);
                unset($_SESSION['Search_Admin']);
                \core\Core::goBack();
                break;

            case 'delete_keyword':
                Search_Admin::deleteKeyword();
                \core\Core::goBack();
                break;

            case 'add_parse_word':
                if (!isset($_REQUEST['keyword'])) {
                    \core\Core::goBack();
                }
                Search_Admin::addParseWord($_REQUEST['keyword']);
                Search_Admin::sendMessage(dgettext('search', 'Keywords added to admin menu.'), 'keyword');
                break;

            case 'drop_keyword':
                if (isset($_SESSION['Search_Add_Words'])) {
                    $array_key = array_search($_REQUEST['kw'], $_SESSION['Search_Add_Words']);
                    if ($array_key !==  FALSE) {
                        unset($_SESSION['Search_Add_Words'][$array_key]);
                    }
                }
                \core\Core::goBack();
                break;

            case 'add_keyword':
                if (!isset($_GET['kw']) || !isset($_GET['key_id'])) {
                    \core\Core::goBack();
                }

                Search_Admin::addKeyword($_GET['kw'], $_GET['key_id']);
                \core\Core::goBack();
                break;

            case 'remove_searchword':
                if (!isset($_GET['kw']) || !isset($_GET['key_id'])) {
                    \core\Core::goBack();
                }

                Search_Admin::removeSearchword($_GET['kw'], $_GET['key_id']);
                \core\Core::goBack();
                break;

            case 'add_ignore':
                if (!isset($_GET['keyword'])) {
                    \core\Core::goBack();
                }
                Search_Admin::setIgnore($_GET['keyword'], 1);
                \core\Core::goBack();
                break;

            case 'remove_ignore':
                if (!isset($_GET['keyword'])) {
                    \core\Core::goBack();
                }
                Search_Admin::setIgnore($_GET['keyword'], 0);
                \core\Core::goBack();
                break;

            case 'save_settings':
                Search_Admin::saveSettings();
                Search_Admin::sendMessage(dgettext('search', 'Settings saved'), 'settings');
                break;
        }

        $template['MESSAGE'] = Search_Admin::getMessage();

        $final = \core\Template::process($template, 'search', 'main.tpl');

        $panel->setContent($final);
        $finalPanel = $panel->display();
        Layout::add(PHPWS_ControlPanel::display($finalPanel));
    }

    public function removeSearchword($keyword, $key_id)
    {
        $search = new Search((int)$key_id);
        if ($search->_error) {
            \core\Error::log($search->_error);
            return;
        }

        $search->removeKeyword($keyword);
        return $search->save();
    }

    public function addKeyword($keyword, $key_id)
    {
        $search = new Search((int)$key_id);
        if ($search->_error) {
            \core\Error::log($search->_error);
            return;
        }

        $search->addKeywords($keyword, FALSE);
        return $search->save();
    }

    public function sendMessage($message, $command)
    {
        $_SESSION['Search_Message'] = $message;
        \core\Core::reroute('index.php?module=search&command=' . $command);
    }

    public static function getMessage()
    {
        if (!isset($_SESSION['Search_Message'])) {
            return NULL;
        }
        $message = $_SESSION['Search_Message'];
        unset($_SESSION['Search_Message']);
        return $message;
    }

    public function addParseWord($words)
    {
        if (!isset($_SESSION['Search_Add_Words'])) {
            $_SESSION['Search_Add_Words'] = $words;
        } else {
            $_SESSION['Search_Add_Words'] = array_merge($_SESSION['Search_Add_Words'], $words);
        }
        $_SESSION['Search_Add_Words'] = array_unique($_SESSION['Search_Add_Words']);
        $_SESSION['Search_Admin'] = TRUE;
    }

    public static function miniAdmin()
    {
        $key = \core\Key::getCurrent();

        if (empty($key) || $key->isDummy() || isset($key->_error)) {
            $on_page = FALSE;
        } else {
            $on_page = TRUE;
        }

        if ($on_page) {
            $search = new Search($key);
            if ($search->keywords) {
                foreach ($search->keywords as $keyword) {
                    $vars['key_id'] = $key->id;
                    $link['WORD'] = $vars['kw'] = $keyword;
                    $vars['command'] = 'remove_searchword';
                    $link['DROP_LINK'] = \core\Text::secureLink(dgettext('search', 'Drop'), 'search', $vars);
                    $tpl['current-words'][] = $link;
                }
            }
            $tpl['CURRENT_TITLE'] = dgettext('search', 'Current keywords');
        }


        if (isset($_SESSION['Search_Add_Words'])) {
            foreach ($_SESSION['Search_Add_Words'] as $keyword) {
                $link = $vars = NULL;
                $link['WORD'] = $vars['kw'] = $keyword;
                $vars['command'] = 'drop_keyword';
                $link['DROP_LINK'] = \core\Text::secureLink(dgettext('search', 'Drop'), 'search', $vars);

                if ($on_page) {
                    if (!in_array($keyword, $search->keywords)) {
                        $vars['key_id'] = $key->id;
                        $vars['command'] = 'add_keyword';
                        $link['ADD_LINK'] = \core\Text::secureLink(dgettext('search', 'Add'), 'search', $vars);
                    }
                }

                $tpl['add-words'][] = $link;
            }
            $tpl['BANK_TITLE'] = dgettext('search', 'Clipped words');
        }

        $tpl['TITLE'] = dgettext('search', 'Search Admin');

        $tpl['CLOSE_LINK'] = \core\Text::secureLink(dgettext('search', 'Close'), 'search', array('module'=>'search', 'command'=>'close_admin'));

        $content = \core\Template::process($tpl, 'search', 'mini_menu.tpl');

        Layout::add($content, 'search', 'admin_box');
    }

    public static function settings()
    {
        $main['TITLE'] = dgettext('search', 'Search Settings');

        $form = new \core\Form('settings');
        $form->addHidden('module', 'search');
        $form->addHidden('command', 'save_settings');

        $form->addCheckBox('show_alternates');
        $form->setLabel('show_alternates', dgettext('search', 'Show alternate options'));
        $form->setMatch('show_alternates', \core\Settings::get('search', 'show_alternates'));

        $form->addSubmit(dgettext('search', 'Save settings'));

        $tpl = $form->getTemplate();

        $main['CONTENT'] = \core\Template::process($tpl, 'search', 'settings.tpl');
        return $main;
    }

    public static function keyword()
    {
        
        $tpl['TITLE'] = dgettext('search', 'Keywords');

        \core\Core::initModClass('search', 'Stats.php');

        $pager = new \core\DBPager('search_stats', 'Search_Stats');
        $pager->setModule('search');
        $pager->setTemplate('pager.tpl');
        $pager->addRowTags('getTplTags');

        $options['keyword'] = '';
        $options['delete_keyword'] = dgettext('search', 'Delete');

        // if entered in search box, remove
        $options['add_ignore'] = dgettext('search', 'Ignore');

        // remember word to add to items
        $options['add_parse_word'] = dgettext('search', 'Clip word');

        $form = new \core\Form('keywords');
        $form->setMethod('get');
        $form->addHidden('module', 'search');
        $form->addSelect('command', $options);

        $template = $form->getTemplate();

        $js_vars['value'] = dgettext('search', 'Go');
        $js_vars['select_id'] = $form->getId('command');
        $js_vars['action_match'] = 'delete_keyword';
        $js_vars['message'] = dgettext('search', 'Are you sure you want to delete the checked item(s)?');
        $template['SUBMIT'] = javascript('select_confirm', $js_vars);

        $template['CHECK_ALL'] = javascript('check_all', array('checkbox_name' => 'keyword[]'));
        $template['KEYWORD_LABEL'] = dgettext('search', 'Keyword');
        $template['SUCCESS_LABEL'] = dgettext('search', 'Success');
        $template['FAILURE_LABEL'] = dgettext('search', 'Failure');
        $template['LAST_CALL_DATE_LABEL'] = dgettext('search', 'Last called');
        $template['HIGHEST_RESULT_LABEL'] = dgettext('search', 'Highest result');
        $template['MIXED_LABEL'] = dgettext('search', 'Mixed');
        $pager->addPageTags($template);
        $pager->addToggle('class="bgcolor1"');
        $pager->setSearch('keyword');
        $pager->addWhere('ignored', 0);

        $tpl['CONTENT'] = $pager->get();

        return $tpl;
    }


    public static function ignore()
    {
        
        $tpl['TITLE'] = dgettext('search', 'Ignored');

        \core\Core::initModClass('search', 'Stats.php');

        $pager = new \core\DBPager('search_stats', 'Search_Stats');
        $pager->setModule('search');
        $pager->setTemplate('ignore.tpl');
        $pager->addRowTags('getTplTags');

        $options['keyword'] = '';
        $options['delete_keyword'] = dgettext('search', 'Delete');

        // if entered in search box, remove
        $options['remove_ignore'] = dgettext('search', 'Allow');

        $form = new \core\Form;
        $form->setMethod('get');
        $form->addHidden('module', 'search');
        $form->addSelect('command', $options);

        $template = $form->getTemplate();

        $js_vars['value'] = dgettext('search', 'Go');
        $js_vars['select_id'] = 'command';
        $js_vars['action_match'] = 'delete_keyword';
        $js_vars['message'] = dgettext('search', 'Are you sure you want to delete the checked item(s)?');

        $template['SUBMIT'] = javascript('select_confirm', $js_vars);

        $template['CHECK_ALL'] = javascript('check_all', array('checkbox_name' => 'keyword[]'));
        $template['KEYWORD_LABEL'] = dgettext('search', 'Keyword');
        $template['TOTAL_QUERY_LABEL'] = dgettext('search', 'Total queries');
        $template['LAST_CALL_DATE_LABEL'] = dgettext('search', 'Last called');
        $pager->addPageTags($template);
        $pager->addToggle('class="bgcolor1"');
        $pager->setSearch('keyword');
        $pager->addWhere('ignored', 1);
        $tpl['CONTENT'] = $pager->get();

        return $tpl;

    }

    public function setIgnore($kw_list, $ignore)
    {
        if (!is_array($kw_list)) {
            return FALSE;
        }
        $db = new \core\DB('search_stats');
        $db->addWhere('keyword', $kw_list);
        $db->addValue('ignored', (int)$ignore);
        return $db->update();
    }

    public static function cpanel()
    {
        \core\Core::initModClass('controlpanel', 'Panel.php');
        $link = 'index.php?module=search';
        $tab['keyword']  = array ('title'=>dgettext('search', 'Keywords'), 'link'=> $link);
        $tab['ignore']   = array ('title'=>dgettext('search', 'Ignore'), 'link'=> $link);
        $tab['settings'] = array ('title'=>dgettext('search', 'Settings'), 'link'=> $link);

        $panel = new PHPWS_Panel('search');
        $panel->quickSetTabs($tab);

        $panel->setModule('search');
        return $panel;
    }

    public function deleteKeyword()
    {
        if (!empty($_GET['keyword'])) {
            $db = new \core\DB('search_stats');
            if (is_array($_GET['keyword'])) {
                foreach ($_GET['keyword'] as $kw) {
                    $db->addWhere('keyword', htmlentities($kw, ENT_QUOTES, 'UTF-8'), '=', 'or');
                }
            } else {
                $db->addWhere('keyword', $_GET['keyword']);
            }
            return $db->delete();
        }
        return true;
    }

    public function saveSettings()
    {
        if (isset($_POST['show_alternates'])) {
            \core\Settings::set('search', 'show_alternates', 1);
        } else {
            \core\Settings::set('search', 'show_alternates', 0);
        }

        \core\Settings::save('search');
    }
}

?>