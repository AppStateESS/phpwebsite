<?php

/**
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @version $Id$
 */

PHPWS_Core::initCoreClass('Form.php');

class Blog_Form {

    /**
     * @param boolean limited   If true, use anonymous submission form
     */
    public static function edit(Blog $blog, $version_id=NULL, $limited=false)
    {
        $form = new PHPWS_Form('edit-blog');
        $form->useBreaker();
        $form->addHidden('module', 'blog');

        if ($limited) {
            PHPWS_Core::initCoreClass('Captcha.php');

            $form->addHidden('action', 'post_suggestion');
            $form->addSubmit('submit', dgettext('blog', 'Suggest entry'));

            if (PHPWS_Settings::get('blog', 'captcha_submissions')) {
                $form->addTplTag('CAPTCHA_IMAGE', Captcha::get());
            }
        } else {

            $form->addHidden('action', 'admin');
            $form->addHidden('command', 'post_entry');

            if (isset($version_id)) {
                $form->addHidden('version_id', $version_id);
                if (Current_User::isUnrestricted('blog')) {
                    $form->addSubmit('approve_entry', dgettext('blog', 'Save Changes and Approve'));
                }
            }

            if ($blog->id || isset($version_id)){
                $form->addHidden('blog_id', $blog->id);
                $form->addSubmit('submit', dgettext('blog', 'Update entry'));
            } else {
                $form->addSubmit('submit', dgettext('blog', 'Add entry'));
            }

            $form->addCheck('allow_comments', 1);
            $form->setLabel('allow_comments', dgettext('blog', 'Allow comments'));
            $form->setMatch('allow_comments', $blog->allow_comments);

            $form->addCheck('allow_anon', 1);
            $form->setLabel('allow_anon', dgettext('blog', 'Allow anonymous comments'));

            $default_approval[0] = dgettext('blog', 'Comments preapproved');
            $default_approval[1] = dgettext('blog', 'Anonymous comments require approval');
            $default_approval[2] = dgettext('blog', 'All comments require approval');

            $form->addSelect('comment_approval', $default_approval);
            $form->setLabel('comment_approval', dgettext('blog', 'Comment approval'));

            if ($blog->id && $blog->approved) {
                PHPWS_Core::initModClass('comments', 'Comments.php');
                $thread = Comments::getThread($blog->key_id);
                $form->setMatch('allow_anon', $thread->allow_anon);
                $form->setMatch('comment_approval', $thread->approval);
            } else {
                $form->setMatch('allow_anon', PHPWS_Settings::get('blog', 'anonymous_comments'));
                $form->setMatch('comment_approval', PHPWS_Settings::get('comments', 'default_approval'));
            }

            $link_choices['none']       = dgettext('blog', 'No link and ignore image link setting');
            $link_choices['default']    = dgettext('blog', 'No link but allow image link setting');
            $link_choices['readmore']   = dgettext('blog', 'Link to read more');
            $link_choices['parent']     = dgettext('blog', 'Link resized image to parent');
            $link_choices['url']        = dgettext('blog', 'Link the url below');

            $form->addSelect('image_link', $link_choices);
            $form->setExtra('image_link', 'onchange="toggleUrl(this)"');
            $form->setLabel('image_link', dgettext('blog', 'Link setting (if image)'));
            if (!isset($link_choices[$blog->image_link])) {
                $url = $blog->image_link;
                $form->addTplTag('OP', '1');
                $match = 'url';
            } else {
                $url = null;
                $form->addTplTag('OP', '.5');
                $match = $blog->image_link;
            }

            $form->setMatch('image_link', $match);
            $form->addText('image_url', $url);
            $form->setSize('image_url', 40);
            $form->setLabel('image_url', dgettext('blog', 'Image url'));
            if ($match == 'url') {
                $form->setDisabled('image_url', false);
            } else {
                $form->setDisabled('image_url', true);
            }

            PHPWS_Core::initModClass('filecabinet', 'Cabinet.php');
            $manager = Cabinet::fileManager('image_id', $blog->image_id);
            $manager->maxImageWidth(PHPWS_Settings::get('blog', 'max_width'));
            $manager->maxImageHeight(PHPWS_Settings::get('blog', 'max_height'));

            $manager->moduleLimit(PHPWS_Settings::get('blog', 'mod_folders_only'));

            if ($manager) {
                $form->addTplTag('FILE_MANAGER', $manager->get());
            }

            $form->addCheck('thumbnail', 1);
            $form->setMatch('thumbnail', $blog->thumbnail);
            $form->setLabel('thumbnail', dgettext('blog', 'Show image or media thumbnail in list view'));
        }

        $form->addText('title', $blog->title);
        $form->setSize('title', 40);
        $form->setLabel('title', dgettext('blog', 'Title'));
        $form->setRequired('title');

        $form->addTextArea('summary', $blog->getSummary());
        if (!$limited) {
            $form->useEditor('summary');
        }
        $form->setRows('summary', '10');
        $form->setCols('summary', '60');
        $form->setLabel('summary', dgettext('blog', 'Summary'));

        $form->addTextArea('entry', $blog->getEntry());
        if (!$limited) {
            $form->useEditor('entry');
        }
        $form->setRows('entry', '10');
        $form->setCols('entry', '60');
        $form->setLabel('entry', dgettext('blog', 'Entry'));

        $form->addText('publish_date', $blog->getPublishDate('%Y/%m/%d %H:%M'));
        $form->setLabel('publish_date', dgettext('blog', 'Publish date/time'));
        $form->setSize('publish_date', 20);

        $form->addText('expire_date', $blog->getExpireDate());
        $form->setLabel('expire_date', dgettext('blog', 'Expire date/time'));
        $form->setSize('expire_date', 20);
        $template = $form->getTemplate();

        $jscal['form_name'] = 'edit-blog';
        $jscal['type']      = 'text_clock';

        $jscal['date_name'] = 'publish_date';

        $template['PUBLISH_CAL'] = javascript('js_calendar', $jscal);

        $jscal['date_name'] = 'expire_date';
        $template['EXPIRE_CAL'] = javascript('js_calendar', $jscal);

        $template['EXAMPLE'] = 'YY/MM/DD HH:MM';
        if ($blog->_error) {
            $template['MESSAGE'] = implode('<br />', $blog->_error);
        }
        return PHPWS_Template::process($template, 'blog', 'edit.tpl');
    }

    public static function settings()
    {
        $form = new PHPWS_Form;
        $form->addHidden('module', 'blog');
        $form->addHidden('action', 'admin');
        $form->addHidden('command', 'post_settings');

        $form->addText('blog_limit', PHPWS_Settings::get('blog', 'blog_limit'));
        $form->setSize('blog_limit', 2, 2);
        $form->setLabel('blog_limit', dgettext('blog', 'Blog view limit'));

        $form->addText('past_entries', PHPWS_Settings::get('blog', 'past_entries'));
        $form->setLabel('past_entries', dgettext('blog', 'Number of past entries'));
        $form->setSize('past_entries', 2, 2);

        $form->addCheck('allow_comments', 1);
        $form->setLabel('allow_comments', dgettext('blog', 'Allow comments by default'));
        $form->setMatch('allow_comments', PHPWS_Settings::get('blog', 'allow_comments'));

        $form->addCheck('captcha_submissions', 1);
        $form->setLabel('captcha_submissions', dgettext('blog', 'CAPTCHA submissions'));
        $form->setMatch('captcha_submissions', PHPWS_Settings::get('blog', 'captcha_submissions'));

        $form->addCheck('anonymous_comments', 1);
        $form->setLabel('anonymous_comments', dgettext('blog', 'Allow anonymous comments by default'));
        $form->setMatch('anonymous_comments', PHPWS_Settings::get('blog', 'anonymous_comments'));

        $form->addCheck('simple_image', 1);
        $form->setLabel('simple_image', dgettext('blog', 'Use Image Manager'));
        $form->setMatch('simple_image', PHPWS_Settings::get('blog', 'simple_image'));

        $form->addCheck('mod_folders_only', 1);
        $form->setLabel('mod_folders_only', dgettext('blog', 'Hide general image folders'));
        $form->setMatch('mod_folders_only', PHPWS_Settings::get('blog', 'mod_folders_only'));

        $form->addCheck('home_page_display', 1);
        $form->setLabel('home_page_display', dgettext('blog', 'Show blog on home page'));
        $form->setMatch('home_page_display', PHPWS_Settings::get('blog', 'home_page_display'));

        $form->addCheck('show_category_links', 1);
        $form->setLabel('show_category_links', dgettext('blog', 'Show category links'));
        $form->setMatch('show_category_links', PHPWS_Settings::get('blog', 'show_category_links'));

        $form->addCheck('show_category_icons', 1);
        $form->setLabel('show_category_icons', dgettext('blog', 'Show category icons'));
        $form->setMatch('show_category_icons', PHPWS_Settings::get('blog', 'show_category_icons'));

        $form->addCheck('single_cat_icon', 1);
        $form->setLabel('single_cat_icon', dgettext('blog', 'Only show one category icon'));
        $form->setMatch('single_cat_icon', PHPWS_Settings::get('blog', 'single_cat_icon'));

        $form->addCheck('logged_users_only', 1);
        $form->setLabel('logged_users_only', dgettext('blog', 'Logged user view only'));
        $form->setMatch('logged_users_only', PHPWS_Settings::get('blog', 'logged_users_only'));

        PHPWS_Core::initModClass('users', 'Action.php');
        $groups = User_Action::getGroups('group');

        if (!empty($groups)) {
            $group_match = array();
            $group_match_str = PHPWS_Settings::get('blog', 'view_only');

            if (!empty($group_match_str)) {
                $group_match = explode(':', $group_match_str);
            }

            $form->addMultiple('view_only', $groups);
            $form->setLabel('view_only', dgettext('blog', 'Limit blog to specific groups'));
            $form->setMatch('view_only', $group_match);
        }

        $show[0] = dgettext('blog', 'Do not show');
        $show[1] = dgettext('blog', 'Only on home page');
        $show[2] = dgettext('blog', 'Always');

        $form->addSelect('show_recent', $show);
        $form->setLabel('show_recent', dgettext('blog', 'Show recent entries'));
        $form->setMatch('show_recent', PHPWS_Settings::get('blog', 'show_recent'));


        $cache_view = PHPWS_Settings::get('blog', 'cache_view');
        $form->addCheck('cache_view', 1);
        $form->setLabel('cache_view', dgettext('blog', 'Cache anonymous view'));
        $form->setMatch('cache_view', $cache_view);
        if (!ALLOW_CACHE_LITE) {
            $form->setDisabled('cache_view');
            $form->addTplTag('RESET_CACHE', dgettext('blog', 'System caching disabled.'));
        } else {
            if ($cache_view) {
                $form->addTplTag('RESET_CACHE', PHPWS_Text::secureLink(dgettext('blog', 'Reset cache'), 'blog', array('action'=>'admin', 'command'=>'reset_cache')));
            }
        }

        $form->addCheck('allow_anonymous_submits', 1);
        $form->setLabel('allow_anonymous_submits', dgettext('blog', 'Allow anonymous submissions'));
        $form->setMatch('allow_anonymous_submits', PHPWS_Settings::get('blog', 'allow_anonymous_submits'));

        $form->addTextField('max_width', PHPWS_Settings::get('blog', 'max_width'));
        $form->setLabel('max_width', dgettext('blog', 'Maximum image width (50-2048)'));
        $form->setSize('max_width', 4,4);

        $form->addTextField('max_height', PHPWS_Settings::get('blog', 'max_height'));
        $form->setLabel('max_height', dgettext('blog', 'Maximum image height (50-2048)'));
        $form->setSize('max_height', 4,4);

        $form->addSubmit(dgettext('blog', 'Save settings'));

        if (Current_User::isDeity()) {
            javascript('datepicker');
            $form->addText('purge_date', date('m/d/Y', time() - 31536000));
            $form->setLabel('purge_date', dgettext('blog', 'Purge all entries before this date'));
            $form->setClass('purge_date', 'datepicker');

            $form->addSubmit('purge_confirm', dgettext('blog', 'Confirm purge'));
        }

        $template = $form->getTemplate();


        if (PHPWS_Settings::get('blog', 'allow_anonymous_submits')) {
            $template['MENU_LINK'] = PHPWS_Text::secureLink(dgettext('blog', 'Clip for menu'), 'blog',
            array('action'=>'admin', 'command'=>'menu_submit_link'));
        }

        $template['VIEW_LABEL']       = dgettext('blog', 'View');
        $template['CATEGORY_LABEL']   = dgettext('blog', 'Category');
        $template['COMMENT_LABEL']    = dgettext('blog', 'Comment');
        $template['SUBMISSION_LABEL'] = dgettext('blog', 'Submission');
        $template['PAST_NOTE']        = dgettext('blog', 'Set to zero to prevent display');

        return PHPWS_Template::process($template, 'blog', 'settings.tpl');
    }
}
?>
