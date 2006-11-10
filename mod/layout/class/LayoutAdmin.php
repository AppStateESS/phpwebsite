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

class Layout_Admin{

    function admin()
    {
        PHPWS_Core::initModClass('controlpanel', 'Panel.php');
        $content = NULL;
        $panel = Layout_Admin::adminPanel();

        if (isset($_REQUEST['command'])) {
            $command = $_REQUEST['command'];
        }
        else {
            $command = $panel->getCurrentTab();
        }

        switch ($command){
        case 'boxes':
            $title = _('Adjust Boxes');
            $content[] = Layout_Admin::boxesForm();
            break;

        case 'turn_off_box_move':
            Layout::moveBoxes(FALSE);
            PHPWS_Core::goBack();
            break;

        case 'post_style_change':
            $result = Layout_Admin::postStyleChange();
            if (PEAR::isError($result)) {
                PHPWS_Error::log($result);
            }
            javascript('close_refresh');
            break;

        case 'changeBoxSettings':
            Layout_Admin::saveBoxSettings();
            if ($_REQUEST['reset_boxes']) {
                unset($_SESSION['Layout_Settings']);
                PHPWS_Core::reroute('index.php?module=layout&action=admin&authkey=' . Current_User::getAuthKey());
            }

            $title = _('Adjust Boxes');
            $template['MESSAGE'] = _('Settings changed.');
            $content[] = Layout_Admin::boxesForm();

            break;

        case 'confirmThemeChange':
            $title = _('Themes');
            if (isset($_POST['confirm'])) {
                Layout_Admin::changeTheme($_POST['theme']);
                $template['MESSAGE'] = _('Theme settings updated.');
            } else {
                Layout::reset();
            }

            $content[] = Layout_Admin::adminThemes();
            break;

        case 'edit_footer':
            $result = Layout_Admin::postFooter();
            if (PEAR::isError($result)){
                PHPWS_Error::log($result);
                $title = _('Error');
                $content[] = _('There was a problem updating the settings.');
            } else {
                $title = _('Footer updated.');
                $content[] = Layout_Admin::editFooter();
            }
            break;


        case 'edit_header':
            $result = Layout_Admin::postHeader();
            if (PEAR::isError($result)){
                $title = _('Error');
                $content[] = _('There was a problem updating the settings.');
            } else {
                $title = _('Header updated.');
                $content[] = Layout_Admin::editHeader();
            }
            break;

        case 'footer':
            $title = _('Edit Footer');
            $content[] = Layout_Admin::editFooter();
            break;

        case 'header':
            $title = _('Edit Header');
            $content[] = Layout_Admin::editHeader();
            break;

        case 'meta':
            $title = _('Edit Meta Tags');
            $content[] = Layout_Admin::metaForm();
            break;

        case 'moveBox':
            $result = Layout_Admin::moveBox();
            if ($result === TRUE) {
                PHPWS_Core::reroute($_SERVER['HTTP_REFERER']);
            }
            break;

        case 'postMeta':
            Layout_Admin::postMeta();
            if (isset($_POST['key_id'])) {
                javascript('close_refresh');
                Layout::nakedDisplay();
                exit();
            }
            Layout::reset();
            $title = _('Edit Meta Tags');
            $template['MESSAGE'] = _('Meta Tags updated.');
            $content[] = Layout_Admin::metaForm();
            break;

        case 'postTheme':
            if ($_POST['default_theme'] != $_SESSION['Layout_Settings']->current_theme) {
                Layout::resetBoxes();
                $title = _('Confirm Theme Change');
                $content[] = _('If you are happy with the change, click the appropiate button.');
                $content[] = _('Failure to respond in ten seconds, reverts phpWebSite to the default theme.');
                $content[] = Layout_Admin::confirmThemeChange();
                $_SESSION['Layout_Settings']->current_theme = $_POST['default_theme'];
                $_SESSION['Layout_Settings']->loadSettings();
                $_SESSION['Layout_Settings']->loadContentVars();
                $_SESSION['Layout_Settings']->loadBoxes();
            } else {
                $title = _('Themes');
                $content[] = Layout_Admin::adminThemes();
            }
            break;

        case 'theme':
            $title = _('Themes');
            $content[] = Layout_Admin::adminThemes();
            break;

        case 'js_style_change':
            $content = Layout_Admin::jsStyleChange();
            if (empty($content)) {
                javascript('close_refresh');
            }
            Layout::nakedDisplay($content, _('Change CSS'));
            break;

        case 'page_meta_tags':
            $content = Layout_Admin::pageMetaTags((int)$_REQUEST['key_id']);
            if (empty($content)) {
                javascript('close_refresh');
            }
            Layout::nakedDisplay($content, _('Set meta tags'));
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

    function jsStyleChange()
    {
        $styles = Layout::getExtraStyles();

        if (empty($styles) || !isset($_REQUEST['key_id'])) {
            exit('wtf');
            return FALSE;
        }

        $styles[0] = _('-- Use default style --');
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
        $form->setLabel('style', _('Style sheet'));
        $form->setMatch('style', $current_style);
        $form->addSubmit(_('Save'));

        $form->addButton('cancel', _('Cancel'));
        $form->setExtra('cancel', 'onclick="window.close()"');

        $template = $form->getTemplate();

        $template['TITLE'] = _('Change CSS');
        
        return PHPWS_Template::process($template, 'layout', 'style_change.tpl');
    }


    function &adminPanel()
    {
        PHPWS_Core::initModClass('controlpanel', 'Panel.php');
        $link = 'index.php?module=layout&amp;action=admin';

        $tabs['boxes']     = array('title'=>_('Boxes'),     'link'=>$link);
        $tabs['meta']      = array('title'=>_('Meta Tags'), 'link'=>$link);
        $tabs['theme']     = array('title'=>_('Themes'),    'link'=>$link);
        $tabs['header']    = array('title'=>_('Header'),    'link'=>$link);
        $tabs['footer']    = array('title'=>_('Footer'),    'link'=>$link);
        
        $panel = new PHPWS_Panel('layout');
        $panel->quickSetTabs($tabs);
        return $panel;
    }

    function adminThemes()
    {
        $form = new PHPWS_Form('themes');
        $form->addHidden('module', 'layout');
        $form->addHidden('action', 'admin');
        $form->addHidden('command', 'postTheme');

        $form->addSubmit('update', _('Update Theme Settings'));
        $themeList = Layout_Admin::getThemeList();
        if (PEAR::isError($themeList)){
            PHPWS_Error::log($themeList);
            return _('There was a problem reading the theme directories.');
        }

        $form->addSelect('default_theme', $themeList);
        $form->reindexValue('default_theme');
        $form->setMatch('default_theme', Layout::getDefaultTheme());
        $form->setLabel('default_theme', _('Default Theme'));

        $template = $form->getTemplate();
        return PHPWS_Template::process($template, 'layout', 'themes.tpl');
    }

    function boxesForm()
    {
        $form = new PHPWS_Form('boxes');
        $form->addHidden('module', 'layout');
        $form->addHidden('action', 'admin');
        $form->addHidden('command', 'changeBoxSettings');
        $form->addRadio('move_boxes',  array(0, 1));
        $form->setLabel('move_boxes', array(_('Disable'), _('Enable')));
        if (Layout::isMoveBox()) {
            $form->setMatch('move_boxes', 1);
        } else {
            $form->setMatch('move_boxes', 0);
        }


        $form->addRadio('reset_boxes', array(0,1));
        $form->setLabel('reset_boxes', array(_('Don\'t reset'), _('Reset boxes')));
        $form->setMatch('reset_boxes', 0);

        $form->addSubmit('submit', _('Change Settings'));

        $template = $form->getTemplate();
        $template['RESET_LEGEND'] = _('Reset boxes');
        $template['MOVE_LEGEND'] = _('Box positioning');

        return PHPWS_Template::process($template, 'layout', 'BoxControl.tpl');
    }


    function changeTheme($theme)
    {
        $_SESSION['Layout_Settings']->default_theme = $theme;
        $result = $_SESSION['Layout_Settings']->saveSettings();
        if (PEAR::isError($result)) {
            PHPWS_Error::log($result);
        }
        Layout::reset();
    }

    function confirmThemeChange()
    {
        Layout::reset();
        $form = new PHPWS_Form('confirmThemeChange');
        $form->addHidden('module', 'layout');
        $form->addHidden('action', 'admin');
        $form->addHidden('command', 'confirmThemeChange');
        $form->addHidden('theme', $_POST['default_theme']);
        $form->addSubmit('confirm', _('Complete the theme change'));
        $form->addSubmit('decline', _('Restore the default theme'));
        $address = 'index.php?module=layout&amp;action=admin&amp;command=confirmThemeChange';
        Layout::metaRoute($address, 10);
        return $form->getMerge();
    }

    function editFooter()
    {
        $form = new PHPWS_Form('edit_footer');
        $form->addHidden('module', 'layout');
        $form->addHidden('action', 'admin');
        $form->addHidden('command', 'edit_footer');

        $form->addCheck('footer_fp_only', 1);
        $form->setMatch('footer_fp_only', PHPWS_Settings::get('layout', 'footer_fp_only'));
        $form->setLabel('footer_fp_only', _('Only show footer on front page'));

        $footer = $_SESSION['Layout_Settings']->footer;

        $form->addTextArea('footer', $footer);
        $form->useEditor('footer');
        $form->setRows('footer', 10);
        $form->setWidth('footer', '80%');

        $form->addSubmit('submit', _('Update Footer'));

        $template = $form->getTemplate();
        return PHPWS_Template::process($template, 'layout', 'edit_footer.tpl');
    }


    function editHeader()
    {
        $form = new PHPWS_Form('edit_header');
        $form->addHidden('module', 'layout');
        $form->addHidden('action', 'admin');
        $form->addHidden('command', 'edit_header');

        $form->addCheck('header_fp_only', 1);
        $form->setMatch('header_fp_only', PHPWS_Settings::get('layout', 'header_fp_only'));
        $form->setLabel('header_fp_only', _('Only show header on front page'));

        $header = $_SESSION['Layout_Settings']->header;

        $form->addTextArea('header', $header);
        $form->useEditor('header');
        $form->setRows('header', 10);
        $form->setWidth('header', '80%');

        $form->addSubmit('submit', _('Update Header'));

        $template = $form->getTemplate();

        return PHPWS_Template::process($template, 'layout', 'edit_header.tpl');
    }

    function getThemeList()
    {
        PHPWS_Core::initCoreClass('File.php');
        return PHPWS_File::readDirectory('themes/', 1);
    }

    /**
     * Form for meta tags. Used for site mata tags and individual key
     * meta tags.
     */
    function metaForm($key_id=0)
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
            $form->addSubmit('reset', _('Restore to default'));
        }
        $form->addHidden('module', 'layout');
        $form->addHidden('action', 'admin');
        $form->addHidden('command', 'postMeta');
        $form->addText('page_title', $page_title);
        $form->setSize('page_title', 40);
        $form->setLabel('page_title', _('Page Title'));
        $form->addTextArea('meta_keywords', $meta_keywords);
        $form->setLabel('meta_keywords', _('Keywords'));
        $form->addTextArea('meta_description', $meta_description);
        $form->setLabel('meta_description', _('Description'));
        $form->addCheckBox('index', 1);
        $form->setMatch('index', $index);
        $form->setLabel('index', _('Allow indexing'));
        $form->addCheckBox('follow', 1);
        $form->setMatch('follow', $follow);
        $form->setLabel('follow', _('Allow link following'));

        $form->addSubmit('submit', _('Update'));

        $template = $form->getTemplate();
        $template['ROBOT_LABEL'] = _('Default Robot Settings');

        return PHPWS_Template::process($template, 'layout', 'metatags.tpl');
    }

    /**
     * Receives the post results of the box change form.
     */
    function moveBox()
    {
        PHPWS_Core::initModClass('layout', 'Box.php');
        $box = new Layout_Box($_POST['box_source']);
        $result = $box->move($_POST['box_dest']);

        if (PEAR::isError($result)){
            PHPWS_Error::log($result);
            Layout::add('An unexpected error occurred when trying to save the new box position.');
            return;
        }

        Layout::resetBoxes();

        return TRUE;
    }

    function postStyleChange()
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

    function postHeader()
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

    function postFooter()
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

    function postMeta()
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

    function saveBoxSettings()
    {
        if ($_REQUEST['move_boxes'] == 1) {
            Layout::moveBoxes(TRUE);
        }
        else {
            Layout::moveBoxes(FALSE);
        }

        if ($_REQUEST['reset_boxes'] == '1') {
            Layout::resetDefaultBoxes();
        }
    }

    function pageMetaTags($key_id)
    {
        $content = Layout_Admin::metaForm($key_id);
        return $content;
    }
}

?>