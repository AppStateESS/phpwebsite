<?php

  /**
   * @author Matthew McNaney <mcnaney at gmail dot com>
   * @version $Id$
   */

PHPWS_Core::initCoreClass('Form.php');

class Blog_Form {
    function edit(&$blog, $version_id=NULL)
    {
        $form = & new PHPWS_Form;
        $form->addHidden('module', 'blog');
        $form->addHidden('action', 'admin');
        $form->addHidden('command', 'post_entry');

        if (isset($version_id)) {
            $form->addHidden('version_id', $version_id);
            if (Current_User::isUnrestricted('blog')) {
                $form->addSubmit('approve_entry', _('Save Changes and Approve'));
            }
        }

        if (isset($blog->id) || isset($version_id)){
            $form->addHidden('blog_id', $blog->id);
            $form->addSubmit('submit', _('Update Entry'));
        } else {
            $form->addSubmit('submit', _('Add Entry'));
        }


        $form->addText('title', $blog->title);
        $form->setSize('title', 40);
        $form->setLabel('title', _('Title'));

        $form->addTextArea('summary', $blog->getSummary());
        $form->useEditor('summary');
        $form->setRows('summary', '10');
        $form->setCols('summary', '60');
        $form->setLabel('summary', _('Summary'));

        $form->addTextArea('entry', $blog->getEntry());
        $form->useEditor('entry');
        $form->setRows('entry', '10');
        $form->setCols('entry', '60');
        $form->setLabel('entry', _('Entry'));

        $form->addCheck('allow_comments', 1);
        $form->setLabeL('allow_comments', _('Allow comments'));
        $form->setMatch('allow_comments', $blog->allow_comments);

        $form->addCheck('allow_anon', 1);
        $form->setLabeL('allow_anon', _('Allow anonymous comments'));
        if ($blog->id) {
            PHPWS_Core::initModClass('comments', 'Comments.php');
            $thread = Comments::getThread($blog->key_id);
            $form->setMatch('allow_anon', $thread->allow_anon);
        }

        $template = $form->getTemplate();

        return PHPWS_Template::process($template, 'blog', 'edit.tpl');
    }
}
?>
