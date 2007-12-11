<?php
  /**
   * @version $Id$
   * @author Matthew McNaney <mcnaney at gmail dot com>
   */

PHPWS_Core::requireInc('pagesmith', 'error_defines.php');
PHPWS_Core::requireConfig('pagesmith');

if (!defined('PS_ALLOWED_HEADER_TAGS')) {
    define('PS_ALLOWED_HEADER_TAGS', '<b><strong><i><u><em>');
}

if (!defined('PS_CHECK_CHAR_LENGTH')) {
    define('PS_CHECK_CHAR_LENGTH', true);
}


class PageSmith {
    var $forms   = null;
    var $panel   = null;

    var $title   = null;
    var $message = null;
    var $content = null;

    var $page    = null;


    function admin()
    {
        if (!Current_User::allow('pagesmith')) {
            Current_User::disallow();
        }
        $this->loadPanel();

        $javascript = false;
        switch ($_REQUEST['aop']) {
        case 'menu':
            $this->loadForms();
            if (!isset($_GET['tab'])) {
                $tab = $this->panel->getCurrentTab();
            } else {
                $tab = & $_GET['tab'];
            } 

            switch ($tab) {
            case 'new':
                $this->loadPage();
                $this->forms->editPage();
                break;
            case 'list':
                $this->forms->pageList();
                break;

            case 'settings':
                $this->forms->settings();
                break;
            }
            break;

        case 'edit_page':
            $this->killSaved();
            $this->loadForms();
            $this->loadPage();
            if (!Current_User::allow('pagesmith', 'edit_page', $this->page->id)) {
                Current_User::disallow();
            }
            $this->page->loadSections(true);
            $this->forms->pageLayout();
            break;

        case 'pick_template':
            $this->killSaved();
            $this->loadForms();
            $this->loadPage();
            $this->page->loadTemplate();
            $this->page->loadSections(true);
            $this->forms->editPage();
            break;

        case 'delete_page':
            if (!Current_User::authorized('pagesmith', 'delete_page')) {
                Current_User::disallow();
            }
            $this->loadPage();
            $this->page->delete();
            PHPWS_Cache::clearCache();
            $this->loadForms();
            $this->forms->pageList();
            break;

        case 'edit_page_header':
            $this->loadForms();
            $this->forms->editPageHeader();
            $javascript = true;
            break;

        case 'edit_page_text':
            $this->loadForms();
            $this->forms->editPageText();
            $javascript = true;
            break;
            
        case 'post_header':
            $this->postHeader();
            break;

        case 'post_text':
            $this->postText();
            break;

        case 'post_page':
            $this->postPage();
            PHPWS_Cache::clearCache();
            PHPWS_Core::reroute($this->page->url());
            break;

        case 'front_page_toggle':
            $this->loadPage();
            $this->page->front_page = (bool)$_GET['fp'];
            $this->page->save();
            $this->loadForms();
            $this->forms->pageList();
            break;

        case 'post_settings':
            if (!Current_User::authorized('pagesmith',null,null,null,true)) {
                Current_User::disallow();
            }
            $this->postSettings();
            $this->message = dgettext('pagesmith', 'Settings saved');
            $this->loadForms();
            $this->forms->settings();
            break;
            
        default:
            PHPWS_Core::errorPage('404');
            break;
        }

        $tpl['TITLE']   = $this->title;
        $tpl['CONTENT'] = $this->content;
        $tpl['MESSAGE'] = $this->message;

        $content = PHPWS_Template::process($tpl, 'pagesmith', 'admin_main.tpl');
        if ($javascript) {
            Layout::nakedDisplay($content);
        } else {
            $this->panel->setContent($content);
            Layout::add(PHPWS_ControlPanel::display($this->panel->display($content)));
        }
    }


    function loadForms()
    {
        PHPWS_Core::initModClass('pagesmith', 'PS_Forms.php');
        $this->forms = new PS_Forms;
        $this->forms->ps = & $this;
    }

    function loadPage()
    {
        PHPWS_Core::initModClass('pagesmith', 'PS_Page.php');
        if (@$_REQUEST['id']) {
            $this->page = new PS_Page($_REQUEST['id']);
        } else {
            $this->page = new PS_Page;
            if (isset($_REQUEST['tpl'])) {
                $this->page->template = $_REQUEST['tpl'];
            }
        }
    }

    function loadPanel()
    {
        PHPWS_Core::initModClass('controlpanel', 'Panel.php');
        $this->panel = new PHPWS_Panel('pagesmith');

        $link = 'index.php?module=pagesmith&amp;aop=menu';
        $tabs['new']  = array('title'=>dgettext('pagesmith', 'New'), 'link'=>$link);
        $tabs['list'] = array('title'=>dgettext('pagesmith', 'List'), 'link'=>$link);
        if (Current_User::isUnrestricted('pagesmith')) {
            $tabs['settings'] = array('title'=>dgettext('pagesmith', 'Settings'), 'link'=>$link);
        }

        $this->panel->quickSetTabs($tabs);
        $this->panel->setModule('pagesmith');
    }

    function pageTplDir()
    {
        return PHPWS_Template::getTemplateDirectory('pagesmith')  . 'page_templates/';
    }


    function postPage()
    {
        $this->loadPage();
        $this->page->loadTemplate();
        $this->page->loadSections(false);

        $post_title = strip_tags($_POST['title']);
        if ($post_title != $this->page->title) {
            $this->page->_title_change = true;
        }
        $this->page->title = & $post_title;

        if (empty($this->page->title)) {
            $this->page->title = dgettext('pagesmith', '(Untitled)');
        }

        if (!is_array($_POST['sections'])) {
            $section_list[] = $_POST['sections'];
        } else {
            $section_list = & $_POST['sections'];
        }

        foreach ($section_list as $section_name) {
            $section = & $this->page->_sections[$section_name];
            if ($section->sectype != 'image') {
                $section->content = $_POST[$section_name];
            } else {
                // set content to trigger test below
                $section->type_id = $_POST[$section_name];
                $section->content = 'image';
            }

            // If this page is an update, or the section has some content
            // put it in the section list.
            if ($this->page->id || !empty($section->content)) {
                $sections[$section_name] = & $section;
            }
        }

        if (!isset($sections)) {
            // All sections were empty, return false
            return false;
        }

        if  (!$this->page->id && PHPWS_Settings::get('pagesmith', 'auto_link')) {
            $menu_link = true;
        } else {
            $menu_link = false;
        }

        $this->page->save();

        if ($menu_link && PHPWS_Core::moduleExists('menu')) {
            if (PHPWS_Core::initModClass('menu', 'Menu.php')) {
                Menu::quickKeyLink($this->page->key_id);
            }
        }

        return true;
    }

    function user()
    {
        switch ($_GET['uop']) {
        case 'view_page':
            $this->viewPage();
            break;
        }
    }


    function viewPage()
    {
        if (empty($this->page)) {
            $this->loadPage();
        }
        if ($this->page->id) {
            $content = $this->page->view();
            if (Current_User::allow('pagesmith', 'edit_page', $this->page->id)) {
                $content .= sprintf('<p class="pagesmith-edit">%s</p>', $this->page->editLink());
            }
            Layout::add($content);
        } else {
            PHPWS_Core::errorPage('404');
        }
    }

    function killSaved()
    {
        $_SESSION['PS_Page'] = null;
        PHPWS_Core::killSession('PS_Page');
    }

    function postHeader()
    {
        PHPWS_Core::initModClass('pagesmith', 'PS_Text.php');
        $header = strip_tags($_POST['header'], PS_ALLOWED_HEADER_TAGS);

        $section = new PS_Text;
        $section->secname = $_POST['section_name'];
        $section->content = PHPWS_Text::parseInput($header);
        $section->setSaved();

        $vars['cnt_section_name'] = $_POST['tpl'] . '-' . $_POST['section_name'];
        $vars['hdn_section_name'] = sprintf('pagesmith_%s', $_POST['section_name']);
        $vars['content'] = addslashes(PHPWS_Text::parseOutput($section->content));
        $vars['hidden_value'] = $section->content;

        Layout::nakedDisplay(javascript('modules/pagesmith/update', $vars));
    }

    function postText()
    {
        $warning = null;
        PHPWS_Core::initModClass('pagesmith', 'PS_Text.php');
        $text = & $_POST['text'];

        $section = new PS_Text;
        $section->secname = $_POST['section_name'];
        $section->content =  preg_replace("@\r\n|\r|\n@", '', $text);
        if (PS_CHECK_CHAR_LENGTH && strlen($section->content) > 65535) {
            $warning = dgettext('pagesmith', "You have exceeded the allowed character limit. The page will not save correctly. Click ok to save the text anyway, cancel to return to previous version.");
        }
        $section->setSaved();

        $vars['cnt_section_name'] = $_POST['tpl'] . '-' . $_POST['section_name'];
        $vars['hdn_section_name'] = sprintf('pagesmith_%s', $_POST['section_name']);
        $vars['content'] = addslashes($section->content);
        $vars['hidden_value'] = PHPWS_Text::parseInput($section->content);
        if ($warning) {
            $vars['warning'] = addslashes($warning);
        }
        Layout::nakedDisplay( javascript('modules/pagesmith/update', $vars));
    }

    function postSettings()
    {
        if (isset($_POST['auto_link'])) {
            PHPWS_Settings::set('pagesmith', 'auto_link', 1);
        } else {
            PHPWS_Settings::set('pagesmith', 'auto_link', 0);
        }

        PHPWS_Settings::save('pagesmith');
    }

}

?>