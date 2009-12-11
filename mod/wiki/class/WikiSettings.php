<?php

/**
 * Wiki for phpWebSite
 *
 * See docs/CREDITS for copyright information
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 *
 * @package Wiki
 * @author Greg Meiste <greg.meiste+github@gmail.com>
 */

class WikiSettings {

    /**
     * Settings Administration
     *
     * @author Greg Meiste <greg.meiste+github@gmail.com>
     */
    function admin()
    {
        if (!Current_User::authorized('wiki', 'edit_settings'))
        {
            Current_User::disallow(dgettext('wiki', 'User attempted access to Wiki Settings administration.'));
            return;
        }

        javascript('jquery');
        PHPWS_Core::initModClass('wiki', 'WikiPage.php');
        PHPWS_Core::initCoreClass('DBPager.php');

        if (isset($_POST['op']) && ($_POST['op'] == 'savesettings'))
        {
            WikiManager::sendMessage(WikiSettings::save(), 'admin');
        }

        $tabs = 1;
        $form = new PHPWS_Form;

        $msg = dgettext('wiki', 'Enabling this setting will show the default wiki page on the home page of the web site.');
        $form->addCheck('show_on_home');
        $form->setMatch('show_on_home', PHPWS_Settings::get('wiki', 'show_on_home'));
        $form->addTplTag('SHOW_ON_HOME_LABEL', javascript('slider', array('link' => dgettext('wiki', 'Show on home page'),
                                                          'id' => 'show_on_home_info',
                                                          'message' => $msg)));
        $form->setTab('show_on_home', $tabs++);

        $msg = dgettext('wiki', 'Enabling this setting will allow all visitors to view the wiki.
                                 When disabled, only registered users can view the wiki.');
        $form->addCheck('allow_anon_view');
        $form->setMatch('allow_anon_view', PHPWS_Settings::get('wiki', 'allow_anon_view'));
        $form->addTplTag('ALLOW_ANON_VIEW_LABEL', javascript('slider', array('link' => dgettext('wiki', 'Allow anonymous viewing'),
                                                             'id' => 'allow_anon_view_info',
                                                             'message' => $msg)));
        $form->setTab('allow_anon_view', $tabs++);

        $msg = dgettext('wiki', 'Enabling this setting will allow all registered users to edit pages.  When disabled, only
                                 registered users with admin privileges can edit pages. Anonymous visitors can never edit pages.');
        $form->addCheck('allow_page_edit');
        $form->setMatch('allow_page_edit', PHPWS_Settings::get('wiki', 'allow_page_edit'));
        $form->addTplTag('ALLOW_PAGE_EDIT_LABEL', javascript('slider', array('link' => dgettext('wiki',
                                                                                                'Allow all registered users to edit pages'),
                                                             'id' => 'allow_page_edit_info',
                                                             'message' => $msg)));
        $form->setTab('allow_page_edit', $tabs++);

        $msg = dgettext('wiki', 'Enabling this setting will allow all registered users to upload images.  When disabled, only
                                 registered users with admin privileges can upload images.  Anonymous visitors can never upload images.');
        $form->addCheck('allow_image_upload');
        $form->setMatch('allow_image_upload', PHPWS_Settings::get('wiki', 'allow_image_upload'));
        $form->addTplTag('ALLOW_IMAGE_UPLOAD_LABEL', javascript('slider',
                                                                array('link' => dgettext('wiki',
                                                                                         'Allow all registered users to upload images'),
                                                                'id' => 'allow_image_upload_info',
                                                                'message' => $msg)));
        $form->setTab('allow_image_upload', $tabs++);

        $msg = dgettext('wiki', 'When enabled, the page text will also be parsed by the BBCode parser instead of just the Text_Wiki
                                 parser. Keep in mind that everything you can do with BBCode can be done with wikitax.');
        $form->addCheck('allow_bbcode');
        $form->setMatch('allow_bbcode', PHPWS_Settings::get('wiki', 'allow_bbcode'));
        $form->addTplTag('ALLOW_BBCODE_LABEL', javascript('slider', array('link' => dgettext('wiki', 'Enable BBCode parser'),
                                                          'id' => 'allow_bbcode_info',
                                                          'message' => $msg)));
        $form->setTab('allow_bbcode', $tabs++);

        $msg = dgettext('wiki', 'When enabled, the extended character set will be supported for wiki page names.  For example,
                                 German umlauts would be allowed in a wiki page name.');
        $form->addCheck('ext_chars_support');
        $form->setMatch('ext_chars_support', PHPWS_Settings::get('wiki', 'ext_chars_support'));
        $form->addTplTag('EXT_CHARS_SUPPORT_LABEL', javascript('slider', array('link' => dgettext('wiki', 'Enable extended character set'),
                                                               'id' => 'ext_chars_support_info',
                                                               'message' => $msg)));
        $form->setTab('ext_chars_support', $tabs++);

        $msg = dgettext('wiki', 'Enabling this setting will add the current wiki page title to the site title which appears in the
                                 browser title bar.  The site title is sometimes used in themes meaning this setting would add the
                                 wiki page title to the theme as well.');
        $form->addCheck('add_to_title');
        $form->setMatch('add_to_title', PHPWS_Settings::get('wiki', 'add_to_title'));
        $form->addTplTag('ADD_TO_TITLE_LABEL', javascript('slider', array('link' => dgettext('wiki', 'Add wiki page title to site title'),
                                                          'id' => 'add_to_title_info',
                                                          'message' => $msg)));
        $form->setTab('add_to_title', $tabs++);

        $msg = dgettext('wiki', 'Enabling this setting will format the current wiki page title before being displayed anywhere
                                 (excluding the wiki page text) by the module.  The page title in the page text will have to be formatted
                                 manually if you do not like the standard WordsSmashedTogether default. The automatic formatting by the
                                 module will add spaces to the WikiPageTitle, making it Wiki Page Title.<br /><br />Remember, you will
                                 still have to refer to the page as WikiPageTitle in the page text, but you can change its appearance by
                                 using [WikiPageTitle Your Formatted Title Here].<br /><br />If this is confusing to you or others, it is
                                 recommended to not use this feature.');
        $form->addCheck('format_title');
        $form->setMatch('format_title', PHPWS_Settings::get('wiki', 'format_title'));
        $form->addTplTag('FORMAT_TITLE_LABEL', javascript('slider', array('link' => dgettext('wiki',
                                                                                             'Format the wiki page title before displaying'),
                                                          'id' => 'format_title_info',
                                                          'message' => $msg)));
        $form->setTab('format_title', $tabs++);

        $msg = dgettext('wiki', 'Enabling this setting will show the <b>Last modified by</b> information on each wiki page. However,
                                 if UPDATED_INFO tag is not in the view template, the information will never show up, regardless of how
                                 this option is set.');
        $form->addCheck('show_modified_info');
        $form->setMatch('show_modified_info', PHPWS_Settings::get('wiki', 'show_modified_info'));
        $form->addTplTag('SHOW_MODIFIED_INFO_LABEL', javascript('slider', array('link' => dgettext('wiki', 'Show page modified information'),
                                                                'id' => 'show_modified_info_info',
                                                                'message' => $msg)));
        $form->setTab('show_modified_info', $tabs++);

        $msg = dgettext('wiki', 'By default, when comparing two page revisions, the changes will be presented in a two column format.
                                 On fixed width layouts this could cause excessive horizontal scrolling.  Setting this option will change
                                 the comparison to a single column format.');
        $form->addCheck('diff_type');
        $form->setMatch('diff_type', (PHPWS_Settings::get('wiki', 'diff_type') == 'one_col'));
        $form->addTplTag('DIFF_TYPE_LABEL', javascript('slider', array('link' => dgettext('wiki', 'Use single column diff'),
                                                       'id' => 'diff_type_info',
                                                       'message' => $msg)));
        $form->setTab('diff_type', $tabs++);

        $msg = dgettext('wiki', 'Enabling this setting will email a notification to the Wiki Administrator email address on every page edit.');
        $form->addCheck('monitor_edits');
        $form->setMatch('monitor_edits', PHPWS_Settings::get('wiki', 'monitor_edits'));
        $form->addTplTag('MONITOR_EDITS_LABEL', javascript('slider', array('link' => dgettext('wiki', 'Monitor Edits'),
                                                           'id' => 'monitor_edits_info',
                                                           'message' => $msg)));
        $form->setTab('monitor_edits', $tabs++);

        $msg = dgettext('wiki', 'Enter in the email address of the Wiki administrator.  If this field is left blank or has an invalid email
                                 address, then the change will be ignored.');
        $form->addText('admin_email', PHPWS_Settings::get('wiki', 'admin_email'));
        $form->setSize('admin_email', 25);
        $form->addTplTag('ADMIN_EMAIL_LABEL', javascript('slider', array('link' => dgettext('wiki', 'Wiki Admin Email'),
                                                         'id' => 'admin_email_info',
                                                         'message' => $msg)));
        $form->setTab('admin_email', $tabs++);

        $msg = dgettext('wiki', 'This is the body text of the email sent when wiki pages are edited.  HTML will be stripped out as the email
                                 will be sent as Plain Text.  You can use variables [page] and [url] to represent the name of the wiki page
                                 and the url to view the page, respectively.');
        $form->addTextArea('email_text', PHPWS_Settings::get('wiki', 'email_text'));
        $form->setWidth('email_text', '80%');
        $form->setRows('email_text', 5);
        $form->addTplTag('EMAIL_TEXT_LABEL', javascript('slider', array('link' => dgettext('wiki', 'Email Notification Text'),
                                                        'id' => 'email_text_info',
                                                        'message' => $msg)));
        $form->setTab('email_text', $tabs++);

        $msg = dgettext('wiki', 'The default page to display when no instructions are passed to the Wiki module.');
        $form->addText('default_page', PHPWS_Settings::get('wiki', 'default_page'));
        $form->setSize('default_page', 25, 100);
        $form->addTplTag('DEFAULT_PAGE_LABEL', javascript('slider', array('link' => dgettext('wiki', 'Default page'),
                                                          'id' => 'default_page_info',
                                                          'message' => $msg)));
        $form->setTab('default_page', $tabs++);

        $msg = dgettext('wiki', 'This controls where external pages will appear. _blank opens the new page in a new window. _parent is
                                 used in the situation where a frameset file is nested inside another frameset file. A link in one of
                                 the inner frameset documents which uses _parent will load the new page where the inner frameset file had
                                 been. If the current page\'s frameset file does not have any parent, then _parent works exactly like
                                 _top - the new document is loaded in the full window. _self puts the new page in the same window and
                                 frame as the current page.');
        $options = array('_blank'=>'_blank', '_parent'=>'_parent', '_self'=>'_self', '_top'=>'_top');
        $form->addSelect('ext_page_target', $options);
        $form->setMatch('ext_page_target', PHPWS_Settings::get('wiki', 'ext_page_target'));
        $form->addTplTag('EXT_PAGE_TARGET_LABEL', javascript('slider', array('link' => dgettext('wiki', 'Target for external links'),
                                                             'id' => 'ext_page_target_info',
                                                             'message' => $msg)));
        $form->setTab('ext_page_target', $tabs++);

        $form->addCheck('immutable_page');
        $form->setMatch('immutable_page', PHPWS_Settings::get('wiki', 'immutable_page'));
        $form->setLabel('immutable_page', dgettext('wiki', 'Show immutable page text (if applicable)'));
        $form->setTab('immutable_page', $tabs++);

        $form->addCheck('raw_text');
        $form->setMatch('raw_text', PHPWS_Settings::get('wiki', 'raw_text'));
        $form->setLabel('raw_text', dgettext('wiki', 'Show raw text link'));
        $form->setTab('raw_text', $tabs++);

        $form->addCheck('print_view');
        $form->setMatch('print_view', PHPWS_Settings::get('wiki', 'print_view'));
        $form->setLabel('print_view', dgettext('wiki', 'Show print view link'));
        $form->setTab('print_view', $tabs++);

        $form->addCheck('what_links_here');
        $form->setMatch('what_links_here', PHPWS_Settings::get('wiki', 'what_links_here'));
        $form->setLabel('what_links_here', dgettext('wiki', 'Show what links here link'));
        $form->setTab('what_links_here', $tabs++);

        $form->addCheck('recent_changes');
        $form->setMatch('recent_changes', PHPWS_Settings::get('wiki', 'recent_changes'));
        $form->setLabel('recent_changes', dgettext('wiki', 'Show recent changes link'));
        $form->setTab('recent_changes', $tabs++);

        $form->addCheck('random_page');
        $form->setMatch('random_page', PHPWS_Settings::get('wiki', 'random_page'));
        $form->setLabel('random_page', dgettext('wiki', 'Show random page link'));
        $form->setTab('random_page', $tabs++);

        $form->addCheck('discussion');
        $form->setMatch('discussion', PHPWS_Settings::get('wiki', 'discussion'));
        $form->setLabel('discussion', dgettext('wiki', 'Enable discussion for registered users'));
        $form->setTab('discussion', $tabs++);

        $form->addCheck('discussion_anon');
        $form->setMatch('discussion_anon', PHPWS_Settings::get('wiki', 'discussion_anon'));
        $form->setLabel('discussion_anon', dgettext('wiki', 'Allow anonymous discussion'));
        $form->setTab('discussion_anon', $tabs++);

        $form->addSubmit('save', dgettext('wiki', 'Save Settings'));
        $form->setTab('save', $tabs);

        $form->addHidden('module', 'wiki');
        $form->addHidden('op', 'savesettings');

        $tags = $form->getTemplate();
        $tags['BACK'] = PHPWS_Text::moduleLink(dgettext('wiki', 'Back to Wiki'), 'wiki');
        $tags['MESSAGE']        = WikiManager::getMessage();
        $tags['MENU_ITEMS_LABEL'] = dgettext('wiki', 'Menu Items');
        $tags['DISCUSSION_SECTION_LABEL'] = dgettext('wiki', 'Discussion');
        $tags['SETTINGS_LABEL'] = dgettext('wiki', 'Settings');
        $tags['PAGES_LABEL']    = dgettext('wiki', 'Wiki Pages');
        $tags['TITLE']          = dgettext('wiki', 'Page Name');
        $tags['UPDATED']        = dgettext('wiki', 'Updated');
        $tags['VERSION']        = dgettext('wiki', 'Version');
        $tags['HITS']           = dgettext('wiki', 'Hits');
        $tags['ORPHANED']       = dgettext('wiki', 'Orphaned');
        $tags['ACTIONS']        = dgettext('wiki', 'Actions');

        $pager = new DBPager('wiki_pages', 'WikiPage');
        $pager->setModule('wiki');
        $pager->setTemplate('admin.tpl');
        $pager->addToggle(PHPWS_LIST_TOGGLE_CLASS);
        $pager->addPageTags($tags);
        $pager->addRowTags('getTpl');
        $pager->setSearch('title', 'pagetext');
        $pager->setDefaultOrder('title', 'asc');
        $pager->cacheQueries();

        $template['TITLE'] = dgettext('wiki', 'Wiki Administration');
        $template['CONTENT'] = $pager->get();
        Layout::add(PHPWS_Template::process($template, 'wiki', 'box.tpl'), 'wiki', 'wiki_mod', TRUE);
    }// END FUNC admin

    /**
     * Save new settings
     *
     * @author Greg Meiste <greg.meiste+github@gmail.com>
     */
    function save()
    {
        PHPWS_Settings::set('wiki', 'show_on_home', (int)isset($_POST['show_on_home']));
        PHPWS_Settings::set('wiki', 'allow_anon_view', (int)isset($_POST['allow_anon_view']));
        PHPWS_Settings::set('wiki', 'allow_page_edit', (int)isset($_POST['allow_page_edit']));
        PHPWS_Settings::set('wiki', 'allow_image_upload', (int)isset($_POST['allow_image_upload']));
        PHPWS_Settings::set('wiki', 'allow_bbcode', (int)isset($_POST['allow_bbcode']));
        PHPWS_Settings::set('wiki', 'ext_chars_support', (int)isset($_POST['ext_chars_support']));
        PHPWS_Settings::set('wiki', 'add_to_title', (int)isset($_POST['add_to_title']));
        PHPWS_Settings::set('wiki', 'format_title', (int)isset($_POST['format_title']));
        PHPWS_Settings::set('wiki', 'show_modified_info', (int)isset($_POST['show_modified_info']));
        PHPWS_Settings::set('wiki', 'monitor_edits', (int)isset($_POST['monitor_edits']));

        if(isset($_POST['diff_type']))
            PHPWS_Settings::set('wiki', 'diff_type', 'one_col');
        else
            PHPWS_Settings::set('wiki', 'diff_type', 'two_col');

        PHPWS_Core::initCoreClass('Mail.php');
        if(isset($_POST['admin_email']) && PHPWS_Mail::checkAddress($_POST['admin_email']))
            PHPWS_Settings::set('wiki', 'admin_email', PHPWS_Text::parseInput($_POST['admin_email']));

        if(isset($_POST['email_text']))
            PHPWS_Settings::set('wiki', 'email_text', PHPWS_Text::parseInput($_POST['email_text']));

        if(isset($_POST['default_page']) && (strlen($_POST['default_page']) > 0))
            PHPWS_Settings::set('wiki', 'default_page', PHPWS_Text::parseInput($_POST['default_page']));

        if(isset($_POST['ext_page_target']))
            PHPWS_Settings::set('wiki', 'ext_page_target', PHPWS_Text::parseInput($_POST['ext_page_target']));

        PHPWS_Settings::set('wiki', 'immutable_page', (int)isset($_POST['immutable_page']));
        PHPWS_Settings::set('wiki', 'raw_text', (int)isset($_POST['raw_text']));
        PHPWS_Settings::set('wiki', 'print_view', (int)isset($_POST['print_view']));
        PHPWS_Settings::set('wiki', 'what_links_here', (int)isset($_POST['what_links_here']));
        PHPWS_Settings::set('wiki', 'recent_changes', (int)isset($_POST['recent_changes']));
        PHPWS_Settings::set('wiki', 'random_page', (int)isset($_POST['random_page']));
        PHPWS_Settings::set('wiki', 'discussion', (int)isset($_POST['discussion']));

        if(isset($_POST['discussion_anon']))
        {
            PHPWS_Settings::set('wiki', 'discussion', 1);
            PHPWS_Settings::set('wiki', 'discussion_anon', 1);
        }
        else
            PHPWS_Settings::set('wiki', 'discussion_anon', 0);

        if (PHPWS_Error::logIfError(PHPWS_Settings::save('wiki')))
        {
            return dgettext('wiki', 'There was an error saving the settings.');
        }

        return dgettext('wiki', 'Your settings have been successfully saved.');
    }// END FUNC _save
}

?>