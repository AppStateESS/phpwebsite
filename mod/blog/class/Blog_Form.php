<?php

  /**
   * @author Matthew McNaney <mcnaney at gmail dot com>
   * @version $Id$
   */

PHPWS_Core::initCoreClass('Form.php');

class Blog_Form {
    function edit(&$blog, $version_id=NULL)
    {
        $key = & new Key($blog->key_id);

        $form = & new PHPWS_Form;
        $form->addHidden('module', 'blog');
        $form->addHidden('action', 'admin');
        $form->addHidden('command', 'postEntry');

        if (isset($version_id)) {
            $form->addHidden('version_id', $version_id);
            if (Current_User::isUnrestricted('blog')) {
                $form->addSubmit('approve_entry', _('Save Changes and Approve'));
            }
        }

        if (isset($blog->id) || isset($version_id)){
            $form->addHidden('blog_id', $blog->id);
            $form->addSubmit('submit', _('Update Entry'));
        } else
            $form->addSubmit('submit', _('Add Entry'));

        $form->addTextArea('entry', $blog->getEntry());
        $form->useEditor('entry');
        $form->setRows('entry', '10');
        $form->setWidth('entry', '80%');
        $form->setLabel('entry', _('Entry'));

        $form->addText('title', $blog->title);
        $form->setSize('title', 40);
        $form->setLabel('title', _('Title'));

        $template = $form->getTemplate();

        return PHPWS_Template::process($template, 'blog', 'edit.tpl');
    }
}
?>
