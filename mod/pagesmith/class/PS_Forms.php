<?php
  /**
   * @version $Id$
   * @author Matthew McNaney <mcnaney at gmail dot com>
   */

class PS_Forms {
    var $template = null;
    var $ps       = null;
    var $tpl_list = null;
    

    function editPage()
    {
        if (!$this->ps->page->id) {
            if (isset($_REQUEST['tpl'])) {
                $this->pageLayout();
            } else {
                $this->pickTemplate();
            }
            return;
        }
    }


    function loadTemplates()
    {
        PHPWS_Core::initModClass('pagesmith', 'PS_Template.php');
        if (!empty($this->tpl_list)) {
            return true;
        }

        $tpl_dir = $this->ps->pageTplDir();
        $templates = PHPWS_File::listDirectories($tpl_dir);

        if (empty($templates)) {
            PHPWS_Error::log(PS_TPL_DIR, 'pagesmith', 'PS_Forms::loadTemplates', $tpl_dir);
            return false;
        }

        foreach ($templates as $tpl) {
            $pg_tpl = new PS_Template($tpl);
            if ($pg_tpl->data) {
                $this->tpl_list[$tpl] = $pg_tpl;
            }
        }
        return true;
    }

    function pageLayout()
    {
        $page = & $this->ps->page;

        $pg_tpl_name = & $page->_tpl->name;
        $this->ps->killSaved();

        $form = new PHPWS_Form('pagesmith');
        $form->addHidden('module', 'pagesmith');
        $form->addHidden('aop', 'post_page');
        $form->addHidden('tpl', $page->template);

        $form->addText('title', $page->title);
        $form->setSize('title', 40, 255);
        $form->setExtra('title', 'onchange="update_title()"');
        $form->setLabel('title', dgettext('pagesmith', 'Page title'));

        if ($page->id) {
            $this->ps->title = dgettext('pagesmith', 'Update page');
            $form->addHidden('id', $page->id);
        } else {
            $this->ps->title = dgettext('pagesmith', 'Create page');
        }

        if (empty($page->_tpl) || $page->_tpl->error) {
            $this->ps->content = dgettext('pagesmith', 'Unable to load page template.');
            return;
        }
        $form->addSubmit('submit', dgettext('pagesmith', 'Save page'));
        $this->pageTemplateForm($form);
        $tpl = $form->getTemplate();
        $jsvars['page_title_input'] = 'pagesmith_title';
        $jsvars['page_title_id'] = sprintf('%s-page-title', $pg_tpl_name);
        javascript('modules/pagesmith/pagetitle', $jsvars);

        $this->ps->content = PHPWS_Template::process($tpl, 'pagesmith', 'page_form.tpl');
    }


    function editPageHeader()
    {
        $section_name = $_GET['section'];

        $vars['parent_section'] = 'pagesmith_' . $section_name;
        $vars['edit_input']     = 'edit_header';
        javascript('modules/pagesmith/passinfo', $vars);

        $form = new PHPWS_Form('edit');
        $form->addHidden('tpl', $_GET['tpl']);
        $form->addHidden('module', 'pagesmith');
        $form->addHidden('aop', 'post_header');
        $form->addHidden('section_name', $section_name);
        $form->addText('header');
        $form->setLabel('header', dgettext('pagesmith', 'Header'));
        $form->setSize('header', 40);
        $form->addSubmit(dgettext('pagesmith', 'Update'));
        $tpl = $form->getTemplate();

        $tpl['CANCEL'] = javascript('close_window');
        $this->ps->title = dgettext('pagesmith', 'Edit header');
        $this->ps->content = PHPWS_Template::process($tpl, 'pagesmith', 'edit_header.tpl');
    }

    function editPageText()
    {
        $section_name = $_GET['section'];

        $vars['parent_section'] = 'pagesmith_' . $section_name;
        $vars['edit_input']     = 'edit_text';
        javascript('modules/pagesmith/passinfo', $vars);

        $form = new PHPWS_Form('edit');
        $form->addHidden('tpl', $_GET['tpl']);
        $form->addHidden('module', 'pagesmith');
        $form->addHidden('aop', 'post_text');
        $form->addHidden('section_name', $section_name);
        $form->addTextArea('text');
        $form->useEditor('text', true, false, 720, 480);
        $form->addSubmit(dgettext('pagesmith', 'Update'));
        $tpl = $form->getTemplate();
        $tpl['CANCEL'] = javascript('close_window');
        $this->ps->title = dgettext('pagesmith', 'Edit header');
        $this->ps->content = PHPWS_Template::process($tpl, 'pagesmith', 'edit_text.tpl');
    }


    function pageList()
    {
        PHPWS_Core::initCoreClass('DBPager.php');
        PHPWS_Core::initModClass('pagesmith', 'PS_Page.php');

        $pgtags['ID_LABEL']      = dgettext('pagesmith', 'Id');
        $pgtags['TITLE_LABEL']   = dgettext('pagesmith', 'Title');
        $pgtags['CREATED_LABEL'] = dgettext('pagesmith', 'Created');
        $pgtags['UPDATED_LABEL'] = dgettext('pagesmith', 'Updated');
        $pgtags['ACTION_LABEL']  = dgettext('pagesmith', 'Action');

        $pager = new DBPager('ps_page', 'PS_Page');
        $pager->addPageTags($pgtags);
        $pager->setModule('pagesmith');
        $pager->setTemplate('page_list.tpl');
        $pager->addRowTags('row_tags');
        $pager->setEmptyMessage(dgettext('pagesmith', 'No pages have been created.'));
        $pager->setSearch('title');

        $this->ps->title   = dgettext('pagesmith', 'Pages');
        $this->ps->content = $pager->get();
    }


    function pageTemplateForm(&$form)
    {
        $page = & $this->ps->page;

        $page->_tpl->loadStyle();
        $vars['id'] = $page->id;
        $vars['tpl'] = $page->template;

        foreach ($page->_sections as $name=>$section) {
            $form->addHidden('sections', $name);
            $content = $section->getContent();
            if (empty($content)) {
                $tpl[$name] = '&nbsp;';
            } else {
                $tpl[$name] = $content;
            }

            if ($section->sectype != 'image') {
                if ($section->sectype == 'header') {
                    $js['label'] = dgettext('pagesmith', 'Edit header');
                    $js['link_title'] = dgettext('pagesmith', 'Change header');
                    $vars['aop'] = 'edit_page_header';
                    $js['width'] = 400;
                    $js['height'] = 200;
                } else {
                    $js['label'] = dgettext('pagesmith', 'Edit text');
                    $js['link_title'] = dgettext('pagesmith', 'Change text');
                    $vars['aop'] = 'edit_page_text';
                    $js['width'] = 800;
                    $js['height'] = 600;
                }

                $vars['section'] = $name;
                //                $js['type'] = 'button';
                $js['label']   = PS_EDIT;
                $js['address'] = PHPWS_Text::linkAddress('pagesmith', $vars, 1);
                $tpl[$name . '_edit'] = javascript('open_window', $js);

                // section session?
                if ($page->id) {
                    $form->addHidden($name, htmlspecialchars($section->content));
                } else {
                    $form->addHidden($name, '');
                }
            }
        }
        $template_file = $page->_tpl->page_path . 'page.tpl';

        if (empty($page->title)) {
            $tpl['page_title'] = dgettext('pagesmith', 'Page Title (edit above)');
        } else {
            $tpl['page_title'] = $page->title;
        }

        $pg_tpl =  PHPWS_Template::process($tpl, 'pagesmith', $template_file);

        $form->addTplTag('PAGE_TEMPLATE', $pg_tpl);
    }

    function pickTemplate()
    {
        Layout::addStyle('pagesmith');
        $this->ps->title = dgettext('pagesmith', 'Pick a template');
        $this->loadTemplates();

        if (empty($this->tpl_list)) {
            $this->ps->content = dgettext('pagesmith', 'Could not find any page templates. Please check your error log.');
        }

        foreach ($this->tpl_list as $pgtpl) {
            $tpl['page-templates'][] = $pgtpl->pickTpl();
        }

        $this->ps->content = PHPWS_Template::process($tpl, 'pagesmith', 'pick_template.tpl');
    }

    function settings()
    {
        $form = new PHPWS_Form('ps-settings');
        $form->addHidden('module', 'pagesmith');
        $form->addHidden('aop', 'post_settings');
        $form->addSubmit(dgettext('pagesmith', 'Save'));

        $form->addCheck('auto_link', 1);
        $form->setMatch('auto_link', PHPWS_Settings::get('pagesmith', 'auto_link'));
        $form->setLabel('auto_link', dgettext('pagesmith', 'Add menu link for new pages.'));

        $this->ps->title = dgettext('pagesmith', 'PageSmith Settings');
        $this->ps->content = PHPWS_Template::process($form->getTemplate(), 'pagesmith', 'settings.tpl');
    }

}

?>