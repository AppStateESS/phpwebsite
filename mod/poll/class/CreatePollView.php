<?php

/**
 * Description
 * @author Jeff Tickle <jtickle at tux dot appstate dot edu>
 */

class CreatePollView // extends View
{
    var $key;

    public function __construct(Key $key = null)
    {
        $this->key = $key;
    }

    public function show($context)
    {
        $form = new PHPWS_Form('create-new-poll');
        $command = PollCommandFactory::getCommand('CreatePoll');
        $command->setKeyId($this->key->id);
        $command->initForm($form);
        $form->addSubmit('submit', dgettext('poll', 'Create Poll'));

        $form->addText('question', $context->get('question'));
        $form->setLabel('question', 'Question');

        $form->addText('response_1', $context->get('response_1'));
        $form->setLabel('response_1', dgettext('poll', 'Response 1'));

        $form->addText('response_2', $context->get('response_2'));
        $form->setLabel('response_2', dgettext('poll', 'Response 2'));

        $tpl = $form->getTemplate();
        $tpl['NO_POLL'] = dgettext('poll', 'There is currently no poll attached to this item.');

        javascript('jquery');
        javascript('modules/poll/CreatePoll');

        return PHPWS_Template::process($tpl, 'poll', 'CreatePollView.tpl');
    }
}
?>
