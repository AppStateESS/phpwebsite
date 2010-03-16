<?php

/**
 * This class handles the administrative functionality
 * for layout. Changing themes, meta tags, etc. is handled
 * here.
 *
 * @author Matthew McNaney <matt at tux dot appstate.edu dot>
 * @version $Id$
 */

define('DEFAULT_LAYOUT_TAB', 'boxes');

class Layout_Admin {
    public function admin()
    {
        if (!Current_User::allow('layout')) {
            Current_User::disallow();
        }
        PHPWS_Core::initModClass('controlpanel', 'Panel.php');
        $content = null;
        $panel = Layout_Admin::adminPanel();

        if (isset($_REQUEST['command'])) {
            $command = $_REQUEST['command'];
        }
        else {
            $command = $panel->getCurrentTab();
        }

        switch ($command){
            case 'arrange':
                $title = dgettext('layout', 'Arrange Layout');
                $content[] = Layout_Admin::arrangeForm();
                break;

            case 'turn_off_box_move':
                Layout::moveBoxes(false);
                PHPWS_Core::goBack();
                break;

            case 'post_style_change':
                $result = Layout_Admin::postStyleChange();
                if (PEAR::isError($result)) {
                    PHPWS_Error::log($result);
                }
                javascript('close_refresh');
                break;

            case 'reset_boxes':
                if (!Current_User::authorized('layout')) {
                    Current_User::disallow();
                }
                Layout::resetDefaultBoxes();
                unset($_SESSION['Layout_Settings']);
                PHPWS_Core::reroute('index.php?module=layout&action=admin&authkey=' . Current_User::getAuthKey());
                break;

            case 'move_boxes_on':
                if (!Current_User::authorized('layout')) {
                    Current_User::disallow();
                }
                Layout::moveBoxes(true);
                PHPWS_Core::goBack();
                break;

            case 'move_boxes_off':
                if (!Current_User::authorized('layout')) {
                    Current_User::disallow();
                }
                Layout::moveBoxes(false);
                PHPWS_Core::goBack();
                break;

            case 'confirmThemeChange':
                $title = dgettext('layout', 'Themes');
                if (isset($_POST['confirm'])) {
                    Layout_Admin::changeTheme();
                    $template['MESSAGE'] = dgettext('layout', 'Theme settings updated.');
                } else {
                    Layout::reset();
                }

                $content[] = Layout_Admin::adminThemes();
                break;

            case 'edit_footer':
                if (!Current_User::authorized('layout')) {
                    Current_User::disallow();
                }
                $result = Layout_Admin::postFooter();
                if (PEAR::isError($result)){
                    PHPWS_Error::log($result);
                    $title = dgettext('layout', 'Error');
                    $content[] = dgettext('layout', 'There was a problem updating the settings.');
                } else {
                    $title = dgettext('layout', 'Footer updated.');
                    $content[] = Layout_Admin::editFooter();
                }
                break;


            case 'edit_header':
                if (!Current_User::authorized('layout')) {
                    Current_User::disallow();
                }
                $result = Layout_Admin::postHeader();
                if (PEAR::isError($result)){
                    $title = dgettext('layout', 'Error');
                    $content[] = dgettext('layout', 'There was a problem updating the settings.');
                } else {
                    $title = dgettext('layout', 'Header updated.');
                    $content[] = Layout_Admin::editHeader();
                }
                break;


            case 'footer':
                $title = dgettext('layout', 'Edit Footer');
                $content[] = Layout_Admin::editFooter();
                break;

            case 'header':
                $title = dgettext('layout', 'Edit Header');
                $content[] = Layout_Admin::editHeader();
                break;

            case 'meta':
                $title = dgettext('layout', 'Edit Meta Tags');
                $content[] = Layout_Admin::metaForm();
                break;

            case 'clear_templates':
                if (!Current_User::authorized('layout')) {
                    Current_User::disallow();
                }
                $files = PHPWS_File::readDirectory('templates/cache', false, true);
                if (!empty($files) && is_array($files)) {
                    foreach ($files as $fn) {
                        @unlink('templates/cache/' . $fn);
                    }
                }
                PHPWS_Core::goBack();
                break;

            case 'clear_cache':
                if (!Current_User::authorized('layout')) {
                    Current_User::disallow();
                }
                PHPWS_Cache::clearCache();
                PHPWS_Core::goBack();
                break;

            case 'moveBox':
                $result = Layout_Admin::moveBox();
                PHPWS_Error::logIfError($result);
                javascript('close_refresh');
                Layout::nakedDisplay();
                break;

            case 'postMeta':
                if (!Current_User::authorized('layout')) {
                    Current_User::disallow();
                }
                Layout_Admin::postMeta();
                if (isset($_POST['key_id'])) {
                    javascript('close_refresh');
                    Layout::nakedDisplay();
                    exit();
                }
                Layout::reset();
                $title = dgettext('layout', 'Edit Meta Tags');
                $template['MESSAGE'] = dgettext('layout', 'Meta Tags updated.');
                $content[] = Layout_Admin::metaForm();
                break;

            case 'demo_fail':
                unset($_SESSION['Layout_Settings']);
                Layout::checkSettings();
                PHPWS_Core::reroute('index.php?module=layout&amp;action=admin&amp;command=confirmThemeChange');
                break;

            case 'demo_theme':
                $title = dgettext('layout', 'Confirm Theme Change');
                $content[] = dgettext('layout', 'If you are happy with the change, click the appropiate button.');
                $content[] = dgettext('layout', 'Failure to respond in ten seconds, reverts phpWebSite to the default theme.');
                $content[] = Layout_Admin::confirmThemeChange();
                break;

            case 'postTheme':
                if (!Current_User::authorized('layout')) {
                    Current_User::disallow();
                }
                if ($_POST['default_theme'] != $_SESSION['Layout_Settings']->current_theme) {
                    Layout::reset($_POST['default_theme']);
                    PHPWS_Core::reroute('index.php?module=layout&action=admin&command=demo_theme&authkey=' . Current_User::getAuthKey());
                } else {
                    PHPWS_Settings::set('layout', 'include_css_order', (int)$_POST['include_css_order']);
                    PHPWS_Settings::save('layout');

                    $title = dgettext('layout', 'Themes');
                    $content[] = Layout_Admin::adminThemes();
                }
                break;

            case 'theme':
                $title = dgettext('layout', 'Themes');
                $content[] = Layout_Admin::adminThemes();
                break;

            case 'js_style_change':
                $content = Layout_Admin::jsStyleChange();
                if (empty($content)) {
                    javascript('close_refresh');
                }
                Layout::nakedDisplay($content, dgettext('layout', 'Change CSS'));
                break;

            case 'page_meta_tags':
                $content = Layout_Admin::pageMetaTags((int)$_REQUEST['key_id']);
                if (empty($content)) {
                    javascript('close_refresh');
                }
                Layout::nakedDisplay($content, dgettext('layout', 'Set meta tags'));
                break;

            case 'move_popup':
                if (!Current_User::authorized('layout')) {
                    Current_User::disallow();
                }
                Layout_Admin::moveBoxMenu();
                break;
        }

        $template['TITLE']   = $title;
        if (isset($content)) {
            $template['CONTENT'] = implode('<br />', $content);
        }
        if (isset($message))
        $template['MESSAGE'] = $message;

        $final = PHPWS_Template::process($template, 'layout', 'main.tpl');
        $panel->setContent($final);

        Layout::add(PHPWS_ControlPanel::display($panel->display()));
    }

    public function jsStyleChange()
    {
        $styles = Layout::getExtraStyles();

        if (empty($styles) || !isset($_REQUEST['key_id'])) {
            return false;
        }
        $styles[0] = dgettext('layout', '-- Use default style --');
        ksort($styles, SORT_NUMERIC);

        $key_id = (int)$_REQUEST['key_id'];

        $current_style = Layout::getKeyStyle($key_id);

        if (empty($current_style)) {
            $current_style = 0;
        }

        $form = new PHPWS_Form('change_styles');
        $form->addHidden('module', 'layout');
        $form->addHidden('action', 'admin');
        $form->addHidden('command', 'post_style_change');
        $form->addHidden('key_id', $key_id);

        $form->addSelect('style', $styles);
        $form->setLabel('style', dgettext('layout', 'Style sheet'));
        $form->setMatch('style', $current_style);
        $form->addSubmit(dgettext('layout', 'Save'));

        $form->addButton('cancel', dgettext('layout', 'Cancel'));
        $form->setExtra('cancel', 'onclick="window.close()"');

        $template = $form->getTemplate();

        $template['TITLE'] = dgettext('layout', 'Change CSS');
        return PHPWS_Template::process($template, 'layout', 'style_change.tpl');
    }


    public function adminPanel()
    {
        PHPWS_Core::initModClass('controlpanel', 'Panel.php');
        $link = 'index.php?module=layout&amp;action=admin';
        $tabs['arrange']   = array('title'=>dgettext('layout', 'Arrange'),   'link'=>$link);
        $tabs['meta']      = array('title'=>dgettext('layout', 'Meta Tags'), 'link'=>$link);
        $tabs['theme']     = array('title'=>dgettext('layout', 'Themes'),    'link'=>$link);
        $tabs['header']    = array('title'=>dgettext('layout', 'Header'),    'link'=>$link);
        $tabs['footer']    = array('title'=>dgettext('layout', 'Footer'),    'link'=>$link);

        $panel = new PHPWS_Panel('layout');
        $panel->quickSetTabs($tabs);
        return $panel;
    }

    public function adminThemes()
    {
        $form = new PHPWS_Form('themes');
        $form->addHidden('module', 'layout');
        $form->addHidden('action', 'admin');
        $form->addHidden('command', 'postTheme');

        $form->addSubmit('update', dgettext('layout', 'Update Theme Settings'));
        $themeList = Layout_Admin::getThemeList();
        if (PEAR::isError($themeList)){
            PHPWS_Error::log($themeList);
            return dgettext('layout', 'There was a problem reading the theme directories.');
        }

        $form->addSelect('default_theme', $themeList);
        $form->reindexValue('default_theme');
        $form->setMatch('default_theme', Layout::getDefaultTheme());
        $form->setLabel('default_theme', dgettext('layout', 'Default Theme'));

        $include_order[0] = dgettext('layout', 'Do not include module style sheets');
        $include_order[1] = dgettext('layout', 'Modules before theme');
        $include_order[2] = dgettext('layout', 'Theme before modules');

        $form->addSelect('include_css_order', $include_order);
        $form->setMatch('include_css_order', PHPWS_Settings::get('layout', 'include_css_order'));
        $form->setLabel('include_css_order', dgettext('layout', 'CSS inclusion order'));

        $template = $form->getTemplate();
        return PHPWS_Template::process($template, 'layout', 'themes.tpl');
    }

    public function arrangeForm()
    {
        $vars['action'] = 'admin';
        $vars['command'] = 'reset_boxes';
        $template['RESET_BOXES'] = PHPWS_Text::secureLink(dgettext('layout', 'Reset boxes'), 'layout', $vars);

        if (Layout::isMoveBox()) {
            $vars['command'] = 'move_boxes_off';
            $label = dgettext('layout', 'Disable box move');
        } else {
            $vars['command'] = 'move_boxes_on';
            $label = dgettext('layout', 'Enable box move');
        }

        $template['MOVE_BOXES']      = PHPWS_Text::secureLink($label, 'layout', $vars);
        $template['MOVE_BOXES_DESC'] = dgettext('layout', 'When enabled, this allows you to shift content to other area of your layout. Movement options depend on the current theme.');
        $template['RESET_DESC']      = dgettext('layout', 'Resets all content back to its original location. Use if problems with Box Move occurred.');

        $vars['command'] = 'clear_templates';
        $template['CLEAR_TEMPLATES']      = PHPWS_Text::secureLink(dgettext('layout', 'Clear templates'), 'layout', $vars);
        $template['CLEAR_TEMPLATES_DESC'] = dgettext('layout', 'Removes all files from the current template cache directory. Good to try if your theme is not displaying properly.');

        $vars['command'] = 'clear_cache';
        $template['CLEAR_CACHE']      = PHPWS_Text::secureLink(dgettext('layout', 'Clear cache'), 'layout', $vars);
        $template['CLEAR_CACHE_DESC'] = dgettext('layout', 'Clears all Cache Lite files. Good to try if module updates do not display.');

        return PHPWS_Template::process($template, 'layout', 'arrange.tpl');
    }


    public function changeTheme()
    {
        $result = $_SESSION['Layout_Settings']->saveSettings();
        if (PEAR::isError($result)) {
            PHPWS_Error::log($result);
        }
        Layout::reset();
    }

    public function confirmThemeChange()
    {
        $form = new PHPWS_Form('confirmThemeChange');
        $form->addHidden('module', 'layout');
        $form->addHidden('action', 'admin');
        $form->addHidden('command', 'confirmThemeChange');
        $form->addSubmit('confirm', dgettext('layout', 'Complete the theme change'));
        $form->addSubmit('decline', dgettext('layout', 'Restore the default theme'));
        $address = 'index.php?module=layout&amp;action=admin&amp;command=demo_fail';
        Layout::metaRoute($address, 10);
        return $form->getMerge();
    }

    public function editFooter()
    {
        $form = new PHPWS_Form('edit_footer');
        $form->addHidden('module', 'layout');
        $form->addHidden('action', 'admin');
        $form->addHidden('command', 'edit_footer');

        $form->addCheck('footer_fp_only', 1);
        $form->setMatch('footer_fp_only', PHPWS_Settings::get('layout', 'footer_fp_only'));
        $form->setLabel('footer_fp_only', dgettext('layout', 'Only show footer on front page'));

        $footer = $_SESSION['Layout_Settings']->footer;

        $form->addTextArea('footer', $footer);
        $form->useEditor('footer');
        $form->setRows('footer', 10);
        $form->setWidth('footer', '80%');

        $form->addSubmit('submit', dgettext('layout', 'Update Footer'));

        $template = $form->getTemplate();
        return PHPWS_Template::process($template, 'layout', 'edit_footer.tpl');
    }


    public function editHeader()
    {
        $form = new PHPWS_Form('edit_header');
        $form->addHidden('module', 'layout');
        $form->addHidden('action', 'admin');
        $form->addHidden('command', 'edit_header');

        $form->addCheck('header_fp_only', 1);
        $form->setMatch('header_fp_only', PHPWS_Settings::get('layout', 'header_fp_only'));
        $form->setLabel('header_fp_only', dgettext('layout', 'Only show header on front page'));

        $header = $_SESSION['Layout_Settings']->header;

        $form->addTextArea('header', $header);
        $form->useEditor('header');
        $form->setRows('header', 10);
        $form->setWidth('header', '80%');

        $form->addSubmit('submit', dgettext('layout', 'Update Header'));

        $template = $form->getTemplate();
        return PHPWS_Template::process($template, 'layout', 'edit_header.tpl');
    }

    public function getThemeList()
    {
        PHPWS_Core::initCoreClass('File.php');
        return PHPWS_File::readDirectory('themes/', 1);
    }

    /**
     * Form for meta tags. Used for site mata tags and individual key
     * meta tags.
     */
    public function metaForm($key_id=0)
    {
        $meta_description = $meta_keywords = $page_title = null;
        $meta_robots = '11';

        if (!$key_id) {
            $vars = $_SESSION['Layout_Settings']->getMetaTags();
        } else {
            $vars = $_SESSION['Layout_Settings']->getPageMetaTags($key_id);
            if (empty($vars)) {
                $vars = $_SESSION['Layout_Settings']->getMetaTags();
                $key = new Key($key_id);
                $vars['page_title'] = $key->title;
            }
        }

        if (is_array($vars)) {
            extract($vars);
        }

        $index = substr($meta_robots, 0, 1);
        $follow = substr($meta_robots, 1, 1);

        $form = new PHPWS_Form('metatags');
        if ($key_id) {
            $form->addHidden('key_id', $key_id);
            $form->addSubmit('reset', dgettext('layout', 'Restore to default'));
        }
        $form->addHidden('module', 'layout');
        $form->addHidden('action', 'admin');
        $form->addHidden('command', 'postMeta');
        $form->addText('page_title', $page_title);
        $form->setSize('page_title', 40);
        $form->setLabel('page_title', dgettext('layout', 'Page Title'));
        $form->addTextArea('meta_keywords', $meta_keywords);
        $form->setLabel('meta_keywords', dgettext('layout', 'Keywords'));
        $form->addTextArea('meta_description', $meta_description);
        $form->setLabel('meta_description', dgettext('layout', 'Description'));
        $form->addCheckBox('index', 1);
        $form->setMatch('index', $index);
        $form->setLabel('index', dgettext('layout', 'Allow indexing'));
        $form->addCheckBox('follow', 1);
        $form->setMatch('follow', $follow);
        $form->setLabel('follow', dgettext('layout', 'Allow link following'));

        $form->addCheckBox('use_key_summaries', 1);
        $form->setMatch('use_key_summaries', PHPWS_Settings::get('layout', 'use_key_summaries'));
        $form->setLabel('use_key_summaries', dgettext('layout', 'Use Key summaries for meta description'));

        $form->addSubmit('submit', dgettext('layout', 'Update'));

        $template = $form->getTemplate();
        $template['ROBOT_LABEL'] = dgettext('layout', 'Default Robot Settings');
        return PHPWS_Template::process($template, 'layout', 'metatags.tpl');
    }

    /**
     * Receives the post results of the box change form.
     */
    public function moveBox()
    {
        PHPWS_Core::initModClass('layout', 'Box.php');
        $box = new Layout_Box($_GET['box_source']);
        $result = $box->move($_GET['box_dest']);

        if (PEAR::isError($result)){
            PHPWS_Error::log($result);
            Layout::add('An unexpected error occurred when trying to save the new box position.');
            return;
        }

        Layout::resetBoxes();

        return true;
    }

    public function postStyleChange()
    {
        Layout::reset();
        if (!isset($_POST['style']) || !isset($_POST['key_id']) ) {
            return;
        }

        $db = new PHPWS_DB('layout_styles');
        $db->addWhere('key_id', (int)$_POST['key_id']);
        $db->delete();
        $db->reset();
        if ($_POST['style'] != '0') {
            $db->addValue('key_id', (int)$_POST['key_id']);
            $db->addValue('style', $_POST['style']);
            $result = $db->insert();

        }
    }

    public function postHeader()
    {
        if (isset($_POST['header_fp_only'])) {
            PHPWS_Settings::set('layout', 'header_fp_only', 1);
        } else {
            PHPWS_Settings::set('layout', 'header_fp_only', 0);
        }

        PHPWS_Settings::save('layout');
        $_SESSION['Layout_Settings']->header = PHPWS_Text::parseInput($_POST['header']);
        return $_SESSION['Layout_Settings']->saveSettings();
    }

    public function postFooter()
    {
        if (isset($_POST['footer_fp_only'])) {
            PHPWS_Settings::set('layout', 'footer_fp_only', 1);
        } else {
            PHPWS_Settings::set('layout', 'footer_fp_only', 0);
        }

        PHPWS_Settings::save('layout');

        $_SESSION['Layout_Settings']->footer = PHPWS_Text::parseInput($_POST['footer']);
        return $_SESSION['Layout_Settings']->saveSettings();
    }

    public function postMeta()
    {
        extract($_POST);

        $values['page_title'] = strip_tags($page_title);
        $values['meta_keywords'] = strip_tags($meta_keywords);
        $values['meta_description'] = strip_tags($meta_description);

        if (isset($_POST['index'])) {
            $index = 1;
        } else {
            $index = 0;
        }

        if (isset($_POST['follow'])) {
            $follow = 1;
        } else {
            $follow = 0;
        }

        PHPWS_Settings::set('layout', 'use_key_summaries', (int)isset($_POST['use_key_summaries']));
        PHPWS_Settings::save('layout');

        $values['meta_robots'] = $index . $follow;

        if (isset($key_id)) {
            $values['key_id'] = $key_id;
            $db = new PHPWS_DB('layout_metatags');
            $db->addWhere('key_id', $key_id);
            $db->delete();
            if (isset($_POST['reset'])) {
                return true;
            }
            $db->reset();
            $db->addValue($values);
            return $db->insert();
        } else {
            $db = new PHPWS_DB('layout_config');
            $db->addValue($values);
            return $db->update();
        }
    }

    public function pageMetaTags($key_id)
    {
        $content = Layout_Admin::metaForm($key_id);
        return $content;
    }

    public function moveBoxMenu()
    {
        $box = new Layout_Box($_GET['box']);
        $vars['action'] = 'admin';
        $vars['command'] = 'moveBox';
        $vars['box_source'] = $box->id;

        $vars['box_dest'] = 'move_box_top';
        $step_links[] = PHPWS_Text::secureLink(dgettext('layout', 'Move to top'), 'layout', $vars);

        $vars['box_dest'] = 'move_box_up';
        $step_links[] = PHPWS_Text::secureLink(dgettext('layout', 'Move up'), 'layout', $vars);

        $vars['box_dest'] = 'move_box_down';
        $step_links[] = PHPWS_Text::secureLink(dgettext('layout', 'Move down'), 'layout', $vars);

        $vars['box_dest'] = 'move_box_bottom';
        $step_links[] = PHPWS_Text::secureLink(dgettext('layout', 'Move to bottom'), 'layout', $vars);

        if (Current_User::isDeity() && !$_SESSION['Layout_Settings']->deity_reload) {
            $_SESSION['Layout_Settings']->loadSettings();
        }

        $themeVars = $_SESSION['Layout_Settings']->getAllowedVariables();

        foreach ($themeVars as $var){
            if ($box->theme_var == $var) {
                continue;
            }
            $vars['box_dest'] = $var;
            $theme_links[] = PHPWS_Text::secureLink(sprintf(dgettext('layout', 'Send to %s'), $var),
                                              'layout', $vars);
        }

        $vars['box_dest'] = 'restore';
        $template['RESTORE'] = PHPWS_Text::secureLink(dgettext('layout', 'Restore to default'), 'layout', $vars);

        $template['STEP_LINKS'] = implode('<br>', $step_links);
        $template['THEME_LINKS'] = implode('<br>', $theme_links);
        $template['CANCEL'] = sprintf('<a href="." onclick="window.close()">%s</a>', dgettext('layout', 'Cancel'));
        $template['TITLE'] = sprintf(dgettext('layout', 'Move box: %s'), $box->content_var);

        $content = PHPWS_Template::process($template, 'layout', 'move_box_select.tpl');
        Layout::nakedDisplay($content);
    }
}

?>