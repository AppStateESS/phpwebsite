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
        $this->ps->killSaved();
        $form = new PHPWS_Form('pagesmith');
        $form->addHidden('module', 'pagesmith');
        $form->addHidden('aop', 'post_page');
        $form->addHidden('tpl', $this->ps->page->template);

        $form->addText('title', $this->ps->page->title);
        $form->setSize('title', 40, 255);
        $form->setLabel('title', dgettext('pagesmith', 'Page title'));

        if ($this->ps->page->id) {
            $this->ps->title = dgettext('pagesmith', 'Update page');
            $form->addHidden('id', $this->ps->page->id);
        } else {
            $this->ps->title = dgettext('pagesmith', 'Create page');
        }

        if (empty($this->ps->page->_tpl) || $this->ps->page->_tpl->error) {
            $this->ps->content = dgettext('pagesmith', 'Unable to load page template.');
            return;
        }
        $form->addSubmit('submit', dgettext('pagesmith', 'Save page'));
        $this->pageTemplateForm($form);
        $tpl = $form->getTemplate();

        $this->ps->content = PHPWS_Template::process($tpl, 'pagesmith', 'page_form.tpl');
    }


    function editPageHeader()
    {
        $section_name = $_GET['section'];
        $form = new PHPWS_Form('edit_header');
        $form->addHidden('tpl', $_GET['tpl']);
        $form->addHidden('module', 'pagesmith');
        $form->addHidden('aop', 'post_header');
        $form->addHidden('section_name', $section_name);
        $form->addText('header', $this->ps->page->getSectionContent($section_name));
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
        $form = new PHPWS_Form('edit_header');
        $form->addHidden('tpl', $_GET['tpl']);
        $form->addHidden('module', 'pagesmith');
        $form->addHidden('aop', 'post_text');
        $form->addHidden('section_name', $section_name);
        $form->addTextArea('text', $this->ps->page->getSectionContent($section_name));
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

        $pgtags['ID_LABEL'] = dgettext('pagesmith', 'Id');
        $pgtags['TITLE_LABEL'] = dgettext('pagesmith', 'Title');

        $pager = new DBPager('ps_page', 'PS_Page');
        $pager->addPageTags($pgtags);
        $pager->setModule('pagesmith');
        $pager->setTemplate('page_list.tpl');
        $pager->addRowTags('row_tags');
        $pager->setEmptyMessage(dgettext('pagesmith', 'No pages have been created.'));

        $this->ps->title   = dgettext('pagesmith', 'Pages');
        $this->ps->content = $pager->get();
    }


    function pageTemplateForm(&$form)
    {
        $this->ps->page->_tpl->loadStyle();
        $vars['id'] = $this->ps->page->id;
        $vars['tpl'] = $this->ps->page->template;

        foreach ($this->ps->page->_sections as $name=>$section) {
            $form->addHidden('sections', $name);
            $tpl[$name] = $section->getContent();
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
                $js['type'] = 'button';
                $js['address'] = PHPWS_Text::linkAddress('pagesmith', $vars, 1);
                $tpl[$name . '_edit'] = javascript('open_window', $js);

                // section session?
                if ($this->ps->page->id) {
                    $form->addHidden($name, htmlspecialchars($section->content));
                } else {
                    $form->addHidden($name, '');
                }
            }
        }
        $template_file = $this->ps->page->_tpl->page_path . 'page.tpl';
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

}

?>