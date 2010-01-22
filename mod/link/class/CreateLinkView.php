<?php

/**
 * Description
 * @author Jeff Tickle <jtickle at tux dot appstate dot edu>
 */

class CreateLinkView // extends View
{
    var $key;

    public function __construct(Key $key = null)
    {
        $this->key = $key;
    }

    public function show($context)
    {
        $form = new PHPWS_Form('create-new-link');
        $command = LinkCommandFactory::getCommand('CreateLink');
        $command->setKeyId($this->key->id);
        $command->initForm($form);
        $form->addSubmit('submit', dgettext('link', 'CreateLink'));

        $form->addText('title', $context->get('title'));
        $form->setLabel('title', 'Title');

        $form->addText('href', $context->get('href'));
        $form->setLabel('href', 'Link Address');

        $form->addText('other', $context->get('other'));
        $form->setLabel('other', 'Other Information');

        $tpl = $form->getTemplate();

        javascript('jquery');
        javascript('modules/link/CreateLink');

        return PHPWS_Template::process($tpl, 'link', 'CreateLinkView.tpl');
    }
}

?>
