<?php

/**
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @version $Id$
 */
PHPWS_Core::initCoreClass('Form.php');

class Blog_Form
{

    /**
     * @param boolean limited   If true, use anonymous submission form
     */
    public static function edit(Blog $blog, $version_id = NULL, $limited = false)
    {
        javascriptMod('blog', 'image_url');
        if ($limited) {
            throw new \Exception('');
        }

        $form = new PHPWS_Form('edit-blog');
        $form->addHidden('module', 'blog');
        $form->addHidden('action', 'admin');
        $form->addHidden('command', 'post_entry');

        if ($blog->id) {
            $form->addHidden('blog_id', $blog->id);
            $form->addSubmit('submit', dgettext('blog', 'Update entry'));
        } else {
            $form->addSubmit('submit', dgettext('blog', 'Add entry'));
        }

        $link_choices['none'] = dgettext('blog', 'No link and ignore image link setting');
        $link_choices['default'] = dgettext('blog', 'No link but allow image link setting');
        $link_choices['readmore'] = dgettext('blog', 'Link to read more');
        $link_choices['parent'] = dgettext('blog', 'Link resized image to parent');
        $link_choices['url'] = dgettext('blog', 'Link the url below');

        $form->addText('title', $blog->title);
        $form->setSize('title', 40);
        $form->setLabel('title', dgettext('blog', 'Title'));
        $form->setRequired('title');

        $form->addTextArea('summary', $blog->getSummaryAndEntry(false));
        if (!$limited) {
            $form->useEditor('summary');
        }
        $form->setRows('summary', '10');
        $form->setCols('summary', '60');
        $form->setLabel('summary', dgettext('blog', 'Content'));

        $form->addText('publish_date', $blog->getPublishDate('%Y/%m/%d %H:%M'));
        $form->setLabel('publish_date', dgettext('blog', 'Publish date/time'));
        $form->setSize('publish_date', 20);
        $form->setClass('publish_date', 'datetimepicker');

        $form->addText('expire_date', $blog->getExpireDate());
        $form->setLabel('expire_date', dgettext('blog', 'Expire date/time'));
        $form->setSize('expire_date', 20);
        $form->setClass('expire_date', 'datetimepicker');
        $template = $form->getTemplate();

        javascript('datetimepicker');

        $jscal['date_name'] = 'expire_date';

        $template['EXAMPLE'] = 'YYYY/MM/DD HH:MM';
        if ($blog->_error) {
            $template['MESSAGE'] = implode('<br />', $blog->_error);
        }
        $template['REMINDER'] = dgettext('blog', 'Add a horizontal rule to separate content into summary and body');
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
        $form->addCssClass('blog_limit', 'form-control');

        $form->addText('past_entries', PHPWS_Settings::get('blog', 'past_entries'));
        $form->setLabel('past_entries', dgettext('blog', 'Number of past entries'));
        $form->setSize('past_entries', 2, 2);
        $form->addCssClass('past_entries', 'form-control');
        // Show/hide posted on date
        $form->addCheck('show_posted_date', 1);
        $form->setLabel('show_posted_date', dgettext('blog', 'Show the date the post was made'));
        $form->setMatch('show_posted_date', PHPWS_Settings::get('blog', 'show_posted_date'));

        // Show/hide posted by user full name
        $form->addCheck('show_posted_by', 1);
        $form->setLabel('show_posted_by', dgettext('blog', 'Show the author\'s name'));
        $form->setMatch('show_posted_by', PHPWS_Settings::get('blog', 'show_posted_by'));

        $form->addCheck('simple_image', 1);
        $form->setLabel('simple_image', dgettext('blog', 'Use Image Manager'));
        $form->setMatch('simple_image', PHPWS_Settings::get('blog', 'simple_image'));

        $form->addCheck('mod_folders_only', 1);
        $form->setLabel('mod_folders_only', dgettext('blog', 'Hide general image folders'));
        $form->setMatch('mod_folders_only', PHPWS_Settings::get('blog', 'mod_folders_only'));

        $form->addCheck('home_page_display', 1);
        $form->setLabel('home_page_display', dgettext('blog', 'Show blog on home page'));
        $form->setMatch('home_page_display', PHPWS_Settings::get('blog', 'home_page_display'));

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
            $form->addCssClass('view_only', 'form-control');
        }

        $show[0] = dgettext('blog', 'Do not show');
        $show[1] = dgettext('blog', 'Only on home page');
        $show[2] = dgettext('blog', 'Always');

        $form->addSelect('show_recent', $show);
        $form->setLabel('show_recent', dgettext('blog', 'Show recent entries'));
        $form->setMatch('show_recent', PHPWS_Settings::get('blog', 'show_recent'));
        $form->addCssClass('show_recent', 'form-control');

        $form->addTextField('max_width', PHPWS_Settings::get('blog', 'max_width'));
        $form->setLabel('max_width', dgettext('blog', 'Maximum image width (50-2048)'));
        $form->setSize('max_width', 4, 4);
        $form->addCssClass('max_width', 'form-control');

        $form->addTextField('max_height', PHPWS_Settings::get('blog', 'max_height'));
        $form->setLabel('max_height', dgettext('blog', 'Maximum image height (50-2048)'));
        $form->setSize('max_height', 4, 4);
        $form->addCssClass('max_height', 'form-control');

        $form->addTextArea('comment_script', PHPWS_Settings::get('blog', 'comment_script'));
        $form->setLabel('comment_script', dgettext('blog', 'Paste in your comment code here (e.g. Disqus, Livefyre, Facebook, etc.)'));
        $form->addCssClass('comment_script', 'form-control');


        $form->addSubmit(dgettext('blog', 'Save settings'));

        if (Current_User::isDeity()) {
            javascript('datepicker');
            $form->addText('purge_date', date('m/d/Y', time() - 31536000));
            $form->setLabel('purge_date', dgettext('blog', 'Purge all entries before this date'));
            $form->addCssClass('purge_date', 'datepicker');
            $form->addCssClass('purge_date', 'form-control');

            $form->addSubmit('purge_confirm', dgettext('blog', 'Confirm purge'));
        }

        $template = $form->getTemplate();

        if (PHPWS_Settings::get('blog', 'allow_anonymous_submits')) {
            $template['MENU_LINK'] = PHPWS_Text::secureLink(dgettext('blog', 'Clip for menu'), 'blog', array('action' => 'admin', 'command' => 'menu_submit_link'));
        }

        $template['VIEW_LABEL'] = dgettext('blog', 'View');
        $template['SUBMISSION_LABEL'] = dgettext('blog', 'Submission');
        $template['PAST_NOTE'] = dgettext('blog', 'Set to zero to prevent display');
        $template['COMMENTS_LABEL'] = dgettext('blog', 'Commenting');

        return PHPWS_Template::process($template, 'blog', 'settings.tpl');
    }

}

?>
