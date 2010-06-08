<?php
Core\Core::initModClass('phpwsbb', 'Topic.php');
Core\Core::initModClass('phpwsbb', 'Forum.php');

/**
 * This class controls the display of all editing forms.
 *
 * @version $Id: BB_Forms.php,v 1.1 2008/08/23 04:19:14 adarkling Exp $
 *
 * @author Eloi George <eloi@NOSPAM.bygeorgeware.com>
 * @module phpwsBB
 */
class PHPWSBB_Forms
{

    /**
     /* Edits the module's default settings.
     *
     * @author Eloi George <eloi@NOSPAM.bygeorgeware.com>
     * @module phpwsbb
     * @param none
     * @return none
     */
    public function edit_configuration ()
    {
        $form = new Core\Form('Config');
        $form->setAction('index.php?module=phpwsbb&op=config');
        $val = Core\Settings::get('phpwsbb');

        /* Section Headers */
        $tags['ANON_HEADER'] = dgettext('phpwsbb', 'Anonymous Users');
        $tags['BLOCKS_HEADER'] = dgettext('phpwsbb', 'Blocks');
        $tags['SPEED_HEADER'] = dgettext('phpwsbb', 'Performance');
        $tags['FORMAT_HEADER'] = dgettext('phpwsbb', 'Text Format');
        $tags['FORMAT_NOTE'] = dgettext('phpwsbb', 'Note: For information on how to format dates, please refer to <a href="http://www.w3schools.com/PHP/func_date_strftime.asp">this page at w3schools.com</a>');

        /* allow_anon_posts */
        $form->addCheckBox('allow_anon_posts');
        $form->setMatch('allow_anon_posts', $val['allow_anon_posts']);
        $form->setLabel('allow_anon_posts', dgettext('phpwsbb', 'Allow Anonymous Posts'));

        /* showforumsblock */
        $form->addCheckBox('showforumsblock');
        $form->setMatch('showforumsblock', $val['showforumsblock']);
        $form->setLabel('showforumsblock', dgettext('phpwsbb', 'Show Forums Block'));

        /* showlatestpostsblock */
        $form->addCheckBox('showlatestpostsblock');
        $form->setMatch('showlatestpostsblock', $val['showlatestpostsblock']);
        $form->setLabel('showlatestpostsblock', dgettext('phpwsbb', 'Show Latest Posts Block'));

        /* maxlatesttopics */
        $arr = range(0,50);
        unset($arr[0]);
        $form->addSelect('maxlatesttopics', $arr);
        $form->setMatch('maxlatesttopics', $val['maxlatesttopics']);
        $form->setLabel('maxlatesttopics', dgettext('phpwsbb', 'Maximum Number of Topics to Show in Latest Posts Block'));

        /* use_views */
        $form->addCheckBox('use_views');
        $form->setMatch('use_views', $val['use_views']);
        $form->setLabel('use_views', dgettext('phpwsbb', 'Record and show number of times a thread is viewed'));
        //		$form->addTplTag('USE_VIEWS_HELP', PHPWS_Help::show_link('phpwsbb', 'views'));

        /* long_date_format */
        $form->addText('long_date_format', $val['long_date_format']);
        $form->setMaxSize('long_date_format', '25');
        $form->setWidth('long_date_format', '50%');
        $form->setLabel('long_date_format', dgettext('phpwsbb', 'Long Date Format'));
        //		$form->addTplTag('DATE_FORMAT_HELP', PHPWS_Help::show_link('phpwsbb', 'date_format'));
        $form->addTplTag('LONG_DATE_FORMAT_EXAMPLE',
        sprintf(dgettext('phpwsbb', 'The current long date format is %1$s, which looks like "%2$s"'),
        $val['long_date_format'], PHPWSBB_Data::get_long_date(time())));

        /* short_date_format */
        $form->addText('short_date_format', $val['short_date_format']);
        $form->setMaxSize('short_date_format', '25');
        $form->setWidth('short_date_format', '50%');
        $form->setLabel('short_date_format', dgettext('phpwsbb', 'Short Date Format'));
        $form->addTplTag('SHORT_DATE_FORMAT_EXAMPLE',
        sprintf(dgettext('phpwsbb', 'The current short date format is %1$s, which looks like "%2$s"'),
        $val['short_date_format'], PHPWSBB_Data::get_short_date(time())));


        $tags['SAVE'] =  '<input name="BB_vars[op:config::save:1]" value="'.dgettext('phpwsbb', 'Save Settings').'" type="submit">';
        $tags['RESET'] =  '<input name="BB_vars[op:config::reset:1]" value="'.dgettext('phpwsbb', 'Reset Settings').'" type="submit">';
        $form->mergeTemplate($tags);
        $form->addhidden('module', 'phpwsbb');

        return Core\Template::processTemplate($form->getTemplate(),'phpwsbb','config.tpl');
    }


    /**
     * Displays a form to move an item to a forum.
     *
     * @author Eloi George <eloi@NOSPAM.bygeorgeware.com>
     * @module phpwsBB
     * @param object $object : Item to move.  Can be either a PHPWSBB_Topic or a Key object.
     * @param bool $popup : Whether to show it as a popup window.
     * @return string : HTML text
     */
    public function assign_forum(&$object, $popup=FALSE)
    {
        if (!$object_class = get_class($object))
        return '';
        $forums = PHPWSBB_Data::get_forum_list();
        if (empty($forums))
        return dgettext('phpwsbb', 'There are no available Forums');

        $form = new Core\Form('forum_list');
        $form->addHidden('module', 'phpwsbb');
        $form->addHidden('op', 'move_topic');
        $form->addHidden('popup', $popup);

        if ($object_class == 'phpwsbb_topic') {
            $topic = $object;
            $form->addHidden('topic', $topic->id);
            $form->addTplTag('ITEM_TITLE', $topic->get_title());
            $form->addTplTag('CURRENT', dgettext('phpwsbb', 'Current Forum'));
            $forum = $topic->get_forum();
            $form->addTplTag('CURRENT_NAME', $forum->get_title());
            $title = sprintf(dgettext('phpwsbb', 'Moving phpwsBB Topic "%s"'),$topic->get_title());
            $str = dgettext('phpwsbb', 'Move this Topic to');
            unset($forums[$topic->fid]);
            $url = 'index.php?module=phpwsbb&amp;view=topic&amp;id='.$topic->id;
        }
        else {
            $key = $object;
            $form->addHidden('key_id', $key->id);
            $form->addTplTag('ITEM_TITLE', $key->title);
            $title = sprintf(dgettext('phpwsbb', 'Assign %1$s "%2$s" to a Forum'),$key->item_name , $key->title);
            $str = dgettext('phpwsbb', 'Make this a Topic in');
            $url = $key->url;
        }
        Layout::addPageTitle($title);
        $form->addTplTag('MOD_TITLE', $title);
        $form->addSelect('new_forum', $forums);
        $form->setLabel('new_forum', $str);
        $form->addSubmit('ASSIGN', dgettext('phpwsbb', 'Assign'));

        if ($popup) {
            $form->addHidden('popup', '1');
            $form->addTplTag('CLOSE_WINDOW', sprintf('<input type="button" value="%s" onclick="opener.location.href=\'%s\'; window.close();" />',
            dgettext('categories', 'Close Window'), $url));
        }
        else
        $form->addTplTag('CLOSE_WINDOW', sprintf('<a href="%s">%s</a>', $url, dgettext('phpwsbb', 'Back to "%s"')));

        $template = $form->getTemplate();
        return Core\Template::process($template, 'phpwsbb', 'assign_forum.tpl');
    }


    /**
     * Displays a cofirmation Dialog (for non-javascript users).
     *
     * @author Eloi George <eloi@NOSPAM.bygeorgeware.com>
     * @module phpwsBB
     * @param string $address : Address to direct the responses to.
     * @param string $question : Confirmation question.
     * @param string $approve : Text to show on the Approval button.
     * @param string $disapprove : Text to show on the Disapproval button.
     * @return string : HTML text
     */
    public function show_dialog($address, $title, $question, $approve = 'Yes', $disapprove = 'No')
    {
        Layout::addPageTitle($title);
        $form = new Core\Form('confirmation_dialog');
        $form->setAction($address);
       	$form->addTplTag('QUESTION', $question);
        $form->addSubmit('yes', $approve);
        $form->addSubmit('no', $disapprove);
        return Core\Template::process($form->getTemplate(), 'phpwsbb', 'confirmation_dialog.tpl');
    }

    /**
     * Displays a form to assign a topic to move comments into.
     *
     * @author Eloi George <eloi@NOSPAM.bygeorgeware.com>
     * @param int $thread_id : id of old thread.
     * @param array $comment_ids : Comments to split.
     * @return string : HTML response.
     */
    public function move_comments ($comment_ids)
    {
        // Get the thread object
        $c_item = new Comment_Item($comment_ids[0]);
        $oldthread = new Comment_Thread($c_item->getThreadId());

        // Get the thread object
        $c_item = new Comment_Item($comment_ids[0]);
        $oldthread = new Comment_Thread($c_item->getThreadId());

        $form = new Core\Form('forum_list');
        $form->addhidden('module', 'phpwsbb');
        $form->addhidden('op', 'move_comments');
        $form->addhidden('oldthread', $oldthread->id);
        $form->addhidden('comment_ids', implode(',', $comment_ids));
        $title = sprintf(dgettext('phpwsbb', 'Moving %1$s Comments From "%2$s" To another Topic'), count($comment_ids), $oldthread->_key->title);
        Layout::addPageTitle($title);
        $form->addTplTag('MOD_TITLE', $title);
        $form->addText('topic');
        $form->setMaxSize('topic', '8');
        $form->setSize('topic', '10');
        $form->setLabel('topic', dgettext('phpwsbb', 'Enter the Id of the topic you are moving these comments to'));
        $form->addCheckBox('leave_notice');
        $form->setMatch('leave_notice', 1);
        $form->setLabel('leave_notice', dgettext('phpwsbb', 'Leave a notice in the old thread'));
        $form->addSubmit(dgettext('phpwsbb', 'Move Comments'));

        $template = $form->getTemplate();
        return Core\Template::process($template, 'phpwsbb', 'move_comments.tpl');
    }

    /**
     * Displays a form to assign a forum to split comments to a new topic.
     *
     * @author Eloi George <eloi@NOSPAM.bygeorgeware.com>
     * @module phpwsBB
     * @param array $comment_ids : Comments to split.
     * @return string : HTML text
     */
    public function split_comments($comment_ids)
    {
        $forums = PHPWSBB_Data::get_forum_list();
        if (empty($forums))
        return dgettext('phpwsbb', 'No forums have been created yet');

        // Get the thread object
        $c_item = new Comment_Item($comment_ids[0]);
        $thread = new Comment_Thread($c_item->getThreadId());

        $form = new Core\Form('forum_list');
        $form->addhidden('module', 'phpwsbb');
        $form->addhidden('op', 'split_comments');
        $form->addhidden('oldthread', $thread->id);
        $form->addhidden('comment_ids', implode(',', $comment_ids));
        $title = sprintf(dgettext('phpwsbb', 'Splitting %1$s Comments From "%2$s" To a New Topic'), count($comment_ids), $thread->_key->title);
        Layout::addPageTitle($title);
        $form->addTplTag('MOD_TITLE', $title);
        $form->addSelect('new_forum', $forums);
        // Get the current forum
        $topic = new PHPWSBB_Topic($thread->id);
        if ($topic->fid)
        $form->setMatch('new_forum', $topic->fid);
        $form->setLabel('new_forum', dgettext('phpwsbb', 'Create a new Topic in'));
        // cm_subject
        $subject = strip_tags(trim($thread->_key->title));
        if (!empty($_POST['cm_subject']))
        $subject = trim($_POST['cm_subject']);
        $form->addText('cm_subject', $subject);
        $form->setMaxSize('cm_subject', '50');
        $form->setWidth('cm_subject', '70%');
        $form->setLabel('cm_subject', dgettext('phpwsbb', 'Topic Title (the tag [Split] will be added to its front)'));
        // leave_notice
        $form->addCheckBox('leave_notice');
        $form->setMatch('leave_notice', 1);
        $form->setLabel('leave_notice', dgettext('phpwsbb', 'Leave a notice in the old thread'));
        $form->addSubmit(dgettext('phpwsbb', 'Split Comments'));

        $template = $form->getTemplate();
        return Core\Template::process($template, 'phpwsbb', 'split_comments.tpl');
    }

}
?>