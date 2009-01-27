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
    public $forms   = null;
    public $panel   = null;

    public $title   = null;
    public $message = null;
    public $content = null;

    public $page    = null;


    public function admin()
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
                if (!Current_User::allow('pagesmith', null, null, null, true)) {
                    Current_User::disallow();
                }
                $this->forms->settings();
                break;

            case 'upload':
                if (!Current_User::allow('pagesmith', 'upload_templates', null, null, true)) {
                    Current_User::disallow();
                }
                $this->forms->uploadTemplates();
                break;
            }
            break;

        case 'edit_page':
            $this->killSaved();
            $this->loadPage();
            if (!$this->page->id) {
                $this->title = dgettext('pagesmith', 'Sorry');
                $this->content = dgettext('pagesmith', 'Page not found');
                break;
            }
            $this->loadForms();
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
            $this->loadPage();
            $this->loadForms();
            $this->forms->editPageHeader();
            $javascript = true;
            break;

        case 'delete_section':
            $this->deleteSection($_GET['sec_id']);
            exit();
            break;

        case 'edit_page_text':
            $this->loadPage();
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
            $result = $this->postPage();

            switch ($result) {
            case -1:
                $this->loadForms();
                $this->page->loadSections(true);
                $this->forms->editPage();
                break;

            case 0:
                $this->message = dgettext('pagesmith', 'Not enough content to create a page.');
                $this->loadForms();
                $this->page->loadSections(true);
                $this->forms->editPage();
                break;

            case 1:
                $this->killSaved();
                PHPWS_Cache::clearCache();
                if (isset($_POST['save_so_far'])) {
                    PHPWS_Core::reroute(PHPWS_Text::linkAddress('pagesmith', array('id'=>$this->page->id, 'aop'=>'edit_page'), true));
                } else {
                    PHPWS_Core::reroute($this->page->url());
                }
                break;
            }
            break;

        case 'front_page_toggle':
            $this->loadPage();
            $this->page->front_page = (bool)$_GET['fp'];
            $this->page->save();
            $this->loadForms();
            $this->forms->pageList();
            break;

        case 'shorten_links':
            if (!Current_User::authorized('pagesmith', 'settings', null, null, true)) {
                Current_User::disallow();
            }
            $this->shortenLinks();
            PHPWS_Core::goBack();
            break;

        case 'lengthen_links':
            if (!Current_User::authorized('pagesmith', 'settings', null, null, true)) {
                Current_User::disallow();
            }
            $this->lengthenLinks();
            PHPWS_Core::goBack();
            break;

        case 'post_settings':
            if (!Current_User::authorized('pagesmith', 'settings', null, null, true)) {
                Current_User::disallow();
            }
            $this->postSettings();
            $this->message = dgettext('pagesmith', 'Settings saved');
            $this->loadForms();
            $this->forms->settings();
            break;

        case 'post_templates':
            if (!Current_User::allow('pagesmith', 'upload_templates', null, null, true)) {
                Current_User::disallow();
            }

            if ($this->postTemplate()) {
                $this->content = dgettext('pagesmith', 'Template posted.');
            } else {
                $this->loadForms();
                $this->forms->uploadTemplates();
            }
            break;

        case 'delete_template':
            if (!Current_User::allow('pagesmith', 'upload_templates', null, null, true)) {
                Current_User::disallow();
            }
            if (!$this->deleteTemplate($_GET['tpl'])) {
                $this->content = dgettext('pagesmith', 'Could not delete page template.');
            } else {
                $this->loadForms();
                $this->forms->uploadTemplates();
            }

            break;

        default:
            PHPWS_Core::errorPage('404');
            break;
        }

        if ($javascript) {
            $tpl['TITLE']   = $this->title;
            $tpl['CONTENT'] = $this->content;
            $tpl['MESSAGE'] = $this->message;
            Layout::nakedDisplay(PHPWS_Template::process($tpl, 'pagesmith', 'admin_main.tpl'));
        } else {
            Layout::add(PHPWS_ControlPanel::display($this->panel->display($this->content, $this->title, $this->message)));
        }
    }


    public function loadForms()
    {
        PHPWS_Core::initModClass('pagesmith', 'PS_Forms.php');
        $this->forms = new PS_Forms;
        $this->forms->ps = & $this;
    }

    public function loadPage()
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
        if (isset($_REQUEST['pid'])) {
            $this->page->parent_page = (int)$_REQUEST['pid'];
        }

        if (isset($_REQUEST['porder'])) {
            $this->page->page_order = (int)$_REQUEST['porder'];
        }

    }

    public function loadPanel()
    {
        PHPWS_Core::initModClass('controlpanel', 'Panel.php');
        $this->panel = new PHPWS_Panel('pagesmith');

        $link = 'index.php?module=pagesmith&amp;aop=menu';
        $tabs['new']  = array('title'=>dgettext('pagesmith', 'New'), 'link'=>$link);
        $tabs['list'] = array('title'=>dgettext('pagesmith', 'List'), 'link'=>$link);
        if (Current_User::isUnrestricted('pagesmith') && Current_User::allow('pagesmith', 'change_settings')) {
            $tabs['settings'] = array('title'=>dgettext('pagesmith', 'Settings'), 'link'=>$link);
        }

        if (Current_User::allow('pagesmith', 'upload_templates') && Current_User::isUnrestricted('pagesmith')) {
            $tabs['upload'] = array('title'=>dgettext('pagesmith', 'Upload'), 'link'=>$link);
        }

        $this->panel->quickSetTabs($tabs);
        $this->panel->setModule('pagesmith');
    }

    public function pageTplDir()
    {
        return PHPWS_Template::getTemplateDirectory('pagesmith')  . 'page_templates/';
    }


    /**
     * Triggered from aop = post_page
     */
    public function postPage()
    {
        $tpl_set = isset($_POST['change_tpl']);
        $this->loadPage();

        if ($this->page->template != $_POST['template_list']) {
            $this->page->loadTemplate($_POST['template_list']);
            $this->page->template = $this->page->_tpl->name;
        } else {
            $this->page->loadTemplate();
        }

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
            if (isset($this->page->_sections[$section_name])) {
                $section = & $this->page->_sections[$section_name];

                if ($section->sectype == 'header' || $section->sectype == 'text') {
                    if (isset($_SESSION['PS_Page'][$this->page->id][$section->secname])) {
                        $section->content = PHPWS_Text::parseInput($_SESSION['PS_Page'][$this->page->id][$section->secname]);
                    } else {
                        $section->content = $_POST[$section_name];
                    }
                    //$section->content = $_POST[$section_name];
                } else {
                    // set content to trigger test below
                    $section->type_id = $_POST[$section_name];
                    $section->content = $section->sectype;
                }
            }

            // If this page is an update, or the section has some content
            // put it in the section list.

            if ($this->page->id || (!empty($section->content) && !(in_array($section->content, array('image', 'document', 'media', 'block')) && !$section->type_id)) ) {
                $sections[$section_name] = & $section;
            }
        }

        if (!isset($sections)) {
            // All sections were empty, return false
            return 0;
        }

        if  (!$this->page->id && !$this->page->parent_page && PHPWS_Settings::get('pagesmith', 'auto_link')) {
            $menu_link = true;
        } else {
            $menu_link = false;
        }

        if (!$tpl_set) {
            $this->page->save();
        }

        if ($menu_link && PHPWS_Core::moduleExists('menu')) {
            if (PHPWS_Core::initModClass('menu', 'Menu.php')) {
                Menu::quickKeyLink($this->page->key_id);
            }
        }

        if ($tpl_set) {
            return -1;
        } else {
            return 1;
        }
    }

    public function user()
    {
        switch ($_GET['uop']) {
        case 'view_page':
            Layout::addStyle('pagesmith');
            $this->viewPage();
            break;
        }
    }


    public function viewPage()
    {
        if (empty($this->page)) {
            $this->loadPage();
        }

        if ($this->page->id) {
            $this->page->loadKey();
            if ($this->page->_key->allowView()) {
                $content = $this->page->view();
                if (Current_User::allow('pagesmith', 'edit_page', $this->page->id)) {
                    $content .= sprintf('<p class="pagesmith-edit">%s</p>', $this->page->editLink());
                }
            } else {
                if  (!Current_User::requireLogin()) {
                    $content = dgettext('pagesmith', 'Restricted page.');
                }
            }
            Layout::add($content);
        } else {
            Layout::add(dgettext('pagesmith', 'Sorry, but your page could not be found. You may wish to search for it.'));
        }
    }

    public function killSaved()
    {
        $_SESSION['PS_Page'] = null;
        PHPWS_Core::killSession('PS_Page');
    }

    public function postHeader()
    {
        PHPWS_Core::initModClass('pagesmith', 'PS_Text.php');
        $header = strip_tags($_POST['header'], PS_ALLOWED_HEADER_TAGS);

        $section = new PS_Text;
        $section->pid = $_POST['pid'];
        $section->secname = $_POST['section_name'];
        $section->content = PHPWS_Text::parseInput($header);
        $section->setSaved();
        $vars['cnt_section_name'] = $_POST['tpl'] . '-' . $_POST['section_name'];
        //$vars['hdn_section_name'] = sprintf('pagesmith_%s', $_POST['section_name']);
        $vars['content'] = addslashes(PHPWS_Text::parseOutput($section->content));
        $vars['hidden_value'] = $section->content;
        Layout::nakedDisplay(javascript('modules/pagesmith/update', $vars));

    }

    public function postText()
    {
        $warning = null;
        PHPWS_Core::initModClass('pagesmith', 'PS_Text.php');
        $text = & $_POST['text'];

        $section = new PS_Text;
        $section->pid = $_POST['pid'];
        $section->secname = $_POST['section_name'];
        $section->content =  preg_replace("@\r\n|\r|\n@", '', $text);
        if (PS_CHECK_CHAR_LENGTH && strlen(PHPWS_Text::parseInput($section->content)) > 65535) {
            $warning = dgettext('pagesmith', "You have exceeded the allowed character limit. The page will not save correctly. Click ok to save the text anyway, cancel to return to previous version.");
        }
        $section->setSaved();

        $vars['cnt_section_name'] = $_POST['tpl'] . '-' . $_POST['section_name'];
        //$vars['hdn_section_name'] = sprintf('pagesmith_%s', $_POST['section_name']);
        $vars['content'] = addslashes($section->content);
        $vars['hidden_value'] = PHPWS_Text::parseInput($section->content);

        if ($warning) {
            $vars['warning'] = addslashes($warning);
        }

        Layout::nakedDisplay( javascript('modules/pagesmith/update', $vars));
    }

    public function postSettings()
    {
        if (isset($_POST['auto_link'])) {
            PHPWS_Settings::set('pagesmith', 'auto_link', 1);
        } else {
            PHPWS_Settings::set('pagesmith', 'auto_link', 0);
        }

        PHPWS_Settings::save('pagesmith');
    }

    private function postTemplate()
    {
        if (preg_match('/\W/', $_POST['template_name'])) {
            $this->message = dgettext('pagesmith', 'The template name must contain alphanumeric characters only.');
            return false;
        } else {
            $template_name = $_POST['template_name'];
            $directory = 'templates/pagesmith/page_templates/' . $template_name . '/';
            if (is_dir($directory)) {
                $this->message = dgettext('pagesmith', 'There is already a template with this name.');
                return false;
            }
        }

        if (empty($_FILES['template_file'])) {
            $this->message = dgettext('pagesmith', 'Missing template file.');
            return false;
        } else {
            $ext = PHPWS_File::getFileExtension($_FILES['template_file']['name']);
            if ($ext != 'tpl' || !PHPWS_File::checkMimeType($_FILES['template_file']['tmp_name'], $ext)) {
                $this->message = dgettext('pagesmith', 'Wrong file type for template file.');
                return false;
            }
        }

        if (empty($_FILES['style_sheet'])) {
            $this->message = dgettext('pagesmith', 'Missing style sheet.');
            return false;
        } else {
            $ext = PHPWS_File::getFileExtension($_FILES['style_sheet']['name']);
            if ($ext != 'css' || !PHPWS_File::checkMimeType($_FILES['style_sheet']['tmp_name'], $ext)) {
                $this->message = dgettext('pagesmith', 'Wrong file type for style sheet.');
                return false;
            }
        }

        if (empty($_FILES['icon'])) {
            $this->message = dgettext('pagesmith', 'Missing icon file.');
            return false;
        } else {
            $ext = PHPWS_File::getFileExtension($_FILES['icon']['name']);

            if ( ($ext != 'png' && $ext != 'jpg' && $ext != 'gif') || !PHPWS_File::checkMimeType($_FILES['icon']['tmp_name'], $ext)) {
                $this->message = dgettext('pagesmith', 'Wrong file type for icon file.');
                return false;
            }
        }

        if (empty($_FILES['structure_file'])) {
            $this->message = dgettext('pagesmith', 'Missing structure file.');
            return false;
        } else {
            $ext = PHPWS_File::getFileExtension($_FILES['structure_file']['name']);

            if ($ext != 'xml' || !PHPWS_File::checkMimeType($_FILES['structure_file']['tmp_name'], $ext)) {
                $this->message = dgettext('pagesmith', 'Wrong file type for structure file.');
                return false;
            }
        }

        if (mkdir($directory)) {
            $this->content = dgettext('pagesmith', 'Template directory created.');
            copy($_FILES['template_file']['tmp_name'], $directory . $_FILES['template_file']['name']);
            copy($_FILES['style_sheet']['tmp_name'], $directory . $_FILES['style_sheet']['name']);
            copy($_FILES['icon']['tmp_name'], $directory . $_FILES['icon']['name']);
            copy($_FILES['structure_file']['tmp_name'], $directory . $_FILES['structure_file']['name']);
            return true;
        } else {
            $this->message = dgettext('pagesmith', 'Unable to create page template directory.');
            return false;
        }
    }

    public function deleteTemplate($tpl)
    {
        if (preg_match('/\W/', $tpl)) {
            return false;
        }
        $template_dir = 'templates/pagesmith/page_templates/' . $tpl;
        return PHPWS_File::rmdir($template_dir);
    }

    public function getTemplateList()
    {
        $tpl_list = PHPWS_File::listDirectories('templates/pagesmith/page_templates/');

        foreach ($tpl_list as $name) {
            $tpl = new PS_Template($name);
            $flist[$name] = $tpl->title;
        }
        return $flist;
    }

    private function deleteSection($sec_id)
    {
        $id = explode('-', $sec_id);
        if ($id[0] == 'text') {
            $db = new PHPWS_DB('ps_text');
        } elseif ($id[0] == 'block') {
            $db = new PHPWS_DB('ps_block');
        } else {
            return;
        }
        $db->addWhere('id', (int)$id[1]);
        PHPWS_Error::logIfError($db->delete());
    }

    private function shortenLinks()
    {
        $db = new PHPWS_DB('menu_links');
        $db->addColumn('id');
        $db->addColumn('url');
        $db->addColumn('key_id');
        $db->addWhere('url', '%index.php?module=pagesmith&uop=view_page%', 'like');
        $result = $db->select();

        if (empty($result)) {
            return true;
        } elseif (PHPWS_Error::logIfError($result)) {
            return false;
        }

        $db->reset();

        $db2 = new PHPWS_DB('phpws_key');

        foreach ($result as $link) {
            $link['url'] = preg_replace('@index.php\?module=pagesmith&uop=view_page&id=(\d+)$@', 'pagesmith/\\1', $link['url']);
            $db->addValue($link);
            $db->addWhere('id', $link['id']);
            if (!PHPWS_Error::logIfError($db->update()) && $link['key_id']) {
                $db2->addValue('url', $link['url']);
                $db2->addWhere('id', $link['key_id']);
                PHPWS_Error::logIfError($db2->update());
                $db2->reset();
            }
            $db->reset();
        }
    }

    private function lengthenLinks()
    {
        $db = new PHPWS_DB('menu_links');
        $db->addColumn('id');
        $db->addColumn('url');
        $db->addColumn('key_id');
        $db->addWhere('url', 'pagesmith/[0-9]+$', 'regexp');
        $result = $db->select();
        if (empty($result)) {
            return true;
        } elseif (PHPWS_Error::logIfError($result)) {
            return false;
        }

        $db->reset();

        $db2 = new PHPWS_DB('phpws_key');

        foreach ($result as $link) {
            $link['url'] = preg_replace('@pagesmith/(\d+)$@', 'index.php?module=pagesmith&uop=view_page&id=\\1', $link['url']);
            $db->addValue($link);
            $db->addWhere('id', $link['id']);
            if (!PHPWS_Error::logIfError($db->update()) && $link['key_id']) {
                $db2->addValue('url', $link['url']);
                $db2->addWhere('id', $link['key_id']);
                PHPWS_Error::logIfError($db2->update());
                $db2->reset();
            }
            $db->reset();
        }
    }
}

?>