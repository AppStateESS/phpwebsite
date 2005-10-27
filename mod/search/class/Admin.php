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

        $options['delete'] = _('Delete');

        // if entered in search box, remove
        $options['add_ignore'] = _('Ignore');

        // remember word to add to items
        $options['add_parse_word'] = _('Grab');

        $form = & new PHPWS_Form;
        $form->addHidden('module', 'search');
        $form->addHidden('command', 'admin');
        $form->addSelect('action', $options);
        $form->addSubmit('submit', _('Go'));
        $template = $form->getTemplate();

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