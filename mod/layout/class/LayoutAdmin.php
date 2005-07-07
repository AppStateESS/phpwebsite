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

    function admin(){
        PHPWS_Core::initModClass('controlpanel', 'Panel.php');
        $content = NULL;
        $panel = Layout_Admin::adminPanel();

        if (isset($_REQUEST['command']))
            $command = $_REQUEST['command'];
        else
            $command = $panel->getCurrentTab();

        switch ($command){
        case 'boxes':
            $title = _('Adjust Boxes');
            $content[] = Layout_Admin::boxesForm();
            break;

        case 'changeBoxSettings':
            Layout_Admin::saveBoxSettings();
            $title = _('Adjust Boxes');
            $template['MESSAGE'] = _('Settings changed');
            $content[] = Layout_Admin::boxesForm();
            break;

        case 'confirmThemeChange':
            $title = _('Themes');
            if (isset($_POST['confirm'])){
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
            if ($result === TRUE)
                PHPWS_Core::reroute($_SERVER['HTTP_REFERER']);
            break;

        case 'postMeta':
            PHPWS_Core::initModClass('layout', 'Initialize.php');
            Layout_Admin::postMeta();
            Layout::reset();
            $title = _('Edit Meta Tags');
            $template['MESSAGE'] = _('Meta Tags updated.');
            $content[] = Layout_Admin::metaForm();
            break;

        case 'postTheme':
            if ($_POST['default_theme'] != $_SESSION['Layout_Settings']->current_theme){
                Layout::resetBoxes();
                $title = _('Confirm Theme Change');
                $content[] = _('If you are happy with the change, click the appropiate button.');
                $content[] = _('Failure to respond in ten seconds, reverts phpWebSite to the default theme.');
                $content[] = Layout_Admin::confirmThemeChange();
                $_SESSION['Layout_Settings']->current_theme = $_POST['default_theme'];
            } else {
                $title = _('Themes');
                $content[] = Layout_Admin::adminThemes();
            }
            break;

        case 'theme':
            $title = _('Themes');
            $content[] = Layout_Admin::adminThemes();
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


    function &adminPanel(){
        PHPWS_Core::initModClass('controlpanel', 'Panel.php');
        $link = 'index.php?module=layout&amp;action=admin';

        $tabs['boxes']     = array('title'=>_('Boxes'),     'link'=>$link);
        $tabs['meta']      = array('title'=>_('Meta Tags'), 'link'=>$link);
        $tabs['theme']     = array('title'=>_('Themes'),    'link'=>$link);
        $tabs['header']    = array('title'=>_('Header'),    'link'=>$link);
        $tabs['footer']    = array('title'=>_('Footer'),    'link'=>$link);

        $panel = & new PHPWS_Panel('layout');
        $panel->quickSetTabs($tabs);
        return $panel;
    }

    function adminThemes(){
        $form = & new PHPWS_Form('themes');
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

    function boxesForm(){
        $form = & new PHPWS_Form('boxes');
        $form->addHidden('module', 'layout');
        $form->addHidden('action', 'admin');
        $form->addHidden('command', 'changeBoxSettings');
        $form->addRadio('move_boxes',  array(0, 1));
        if (Layout::isMoveBox()) {
            $form->setMatch('move_boxes', 1);
        } else {
            $form->setMatch('move_boxes', 0);
        }

        $form->addSubmit('submit', _('Change Settings'));

        $template = $form->getTemplate();

        $template['MOVE_BOX_LABEL'] = _('Adjust Site Layout');
        $template['MOVE_BOXES_ON']  = _('On');
        $template['MOVE_BOXES_OFF']  = _('Off');
        return PHPWS_Template::process($template, 'layout', 'BoxControl.tpl');
    }


    function changeTheme($theme){
        $_SESSION['Layout_Settings']->default_theme = $theme;
        $_SESSION['Layout_Settings']->saveSettings();
        Layout::reset();
    }

    function confirmThemeChange(){
        Layout::reset();
        $form = & new PHPWS_Form('confirmThemeChange');
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

    function editFooter(){
        PHPWS_Core::initCoreClass('Editor.php');
        $form = & new PHPWS_Form('edit_header');
        $form->addHidden('module', 'layout');
        $form->addHidden('action', 'admin');
        $form->addHidden('command', 'edit_footer');

        $footer = $_SESSION['Layout_Settings']->footer;

        if (Editor::willWork()){
            $editor = & new Editor('htmlarea', 'footer', $footer);
            $headInfo = $editor->get();
            $form->addTplTag('FOOTER', $headInfo);
        } else {
            $form->addTextArea('footer', $footer);
            $form->setRows('footer', 10);
            $form->setWidth('footer', '80%');
        }

        $form->addSubmit('submit', _('Update Footer'));

        $template = $form->getTemplate();
        return PHPWS_Template::process($template, 'layout', 'edit_footer.tpl');
    }


    function editHeader(){
        PHPWS_Core::initCoreClass('Editor.php');
        $form = & new PHPWS_Form('edit_header');
        $form->addHidden('module', 'layout');
        $form->addHidden('action', 'admin');
        $form->addHidden('command', 'edit_header');

        $header = $_SESSION['Layout_Settings']->header;

        if (Editor::willWork()){
            $editor = & new Editor('htmlarea', 'header', $header);
            $headInfo = $editor->get();
            $form->addTplTag('HEADER', $headInfo);
        } else {
            $form->addTextArea('header', $header);
            $form->setRows('header', 10);
            $form->setWidth('header', '80%');
        }

        $form->addSubmit('submit', _('Update Header'));

        $template = $form->getTemplate();
        return PHPWS_Template::process($template, 'layout', 'edit_header.tpl');
    
    }

    function getThemeList(){
        PHPWS_Core::initCoreClass('File.php');
        return PHPWS_File::readDirectory('themes/', 1);
    }

    function metaForm(){
        extract($_SESSION['Layout_Settings']->getMetaTags());

        $index = substr($meta_robots, 0, 1);
        $follow = substr($meta_robots, 1, 1);

        $form = & new PHPWS_Form('metatags');
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
        $form->setLabel('index', _('Allow Indexing'));
        $form->addCheckBox('follow', 1);
        $form->setMatch('follow', $follow);
        $form->setLabel('follow', _('Allow Link Following'));

        $form->addSubmit('submit', _('Update'));

        $template = $form->getTemplate();
        $template['ROBOT_LABEL'] = _('Default Robot Settings');

        return PHPWS_Template::process($template, 'layout', 'metatags.tpl');
    }

    function moveBox(){
        PHPWS_Core::initModClass('layout', 'Box.php');
        $box = new Layout_Box($_POST['box_source']);

        $currentThemeVar = $box->getThemeVar();

        if ($_POST['box_dest'] == 'up')
            $box->moveUp();
        elseif ($_POST['box_dest'] == 'down')
            $box->moveDown();
        else {
            $box->setThemeVar($_POST['box_dest']);
            $box->setBoxOrder(NULL);
            $result = $box->save();
        }

        if (PEAR::isError($result)){
            PHPWS_Error::log($result);
            Layout::add('An unexpected error occurred when trying to save the new box position.');
            return;
        }

        Layout_Box::reorderBoxes($box->getTheme(), $currentThemeVar);
        Layout::resetBoxes();
        return TRUE;
    }

    function postHeader(){
        $_SESSION['Layout_Settings']->header = PHPWS_Text::parseInput($_POST['header']);
        return $_SESSION['Layout_Settings']->saveSettings();
    }

    function postFooter(){
        $_SESSION['Layout_Settings']->footer = PHPWS_Text::parseInput($_POST['footer']);
        return $_SESSION['Layout_Settings']->saveSettings();
    }

    function postMeta(){
        extract($_POST);
    
        $values['page_title'] = strip_tags($page_title);
        $values['meta_keywords'] = strip_tags($meta_keywords);
        $values['meta_description'] = strip_tags($meta_description);

        if (isset($_POST['index']))
            $index = 1;
        else
            $index = 0;

        if (isset($_POST['follow']))
            $follow = 1;
        else
            $follow = 0;

        $values['meta_robots'] = $index . $follow;
    
        $db = & new PHPWS_DB('layout_config');
        $db->addValue($values);
        $db->update();
    }

    function postTheme(){
        echo 'post';
    }

    function saveBoxSettings(){
        if ($_REQUEST['move_boxes'] == 1)
            Layout::moveBoxes(TRUE);
        else
            Layout::moveBoxes(FALSE);
    }


}

?>