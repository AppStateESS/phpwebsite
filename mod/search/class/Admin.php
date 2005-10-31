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

        if (isset($_REQUEST['action'])) {
            $command = $_REQUEST['action'];
        } elseif (isset($_REQUEST['tab'])) {
            $command = $_REQUEST['tab'];
        } else {
            $command = $panel->getCurrentTab();
        }

        switch ($command) {
        case 'keyword':
            $template = Search_Admin::keyword();
            break;

        case 'delete_keyword':
            if (!Current_User::authorized('search')) {
                Current_User::disallow();
            }

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

        }

        $final = PHPWS_Template::process($template, 'search', 'main.tpl');

        $panel->setContent($final);
        $finalPanel = $panel->display();
        Layout::add(PHPWS_ControlPanel::display($finalPanel));
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
        $options['add_parse_word'] = _('Grab');

        $form = & new PHPWS_Form;
        $form->setMethod('get');
        $form->addHidden('module', 'search');
        $form->addHidden('command', 'admin');
        $form->addSelect('action', $options);

        $template = $form->getTemplate();

        $js_vars['value'] = _('Go');
        $js_vars['select_id'] = 'action';
        $js_vars['action_match'] = 'delete_keyword';
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
        $link = 'index.php?module=search&amp;command=admin';
        $tab['keyword'] = array ('title'=>_('Keywords'), 'link'=> $link);

        $panel = & new PHPWS_Panel('search');
        $panel->quickSetTabs($tab);

        $panel->setModule('search');
        return $panel;
    }

}

?>