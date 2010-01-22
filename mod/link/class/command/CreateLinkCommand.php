<?php

/**
 * Description
 * @author Jeff Tickle <jtickle at tux dot appstate dot edu>
 */

class CreateLinkCommand extends LinkCommand
{
    private $key_id;

    public function getRequestVars()
    {
        $vars = array('action' => 'CreateLink');

        if(isset($this->key_id)) {
            $vars['key_id'] = $this->key_id;
        }

        return $vars;
    }

    public function setKeyId($key_id)
    {
        $this->key_id = $key_id;
    }

    public function execute(LinkContext $context)
    {
        if(!isset($this->key_id)) {
            $this->key_id = $context->get('key_id');
        }

        $key_id = $this->key_id;
        $title = $context->get('title');
        $href = $context->get('href');
        $other = $context->get('other');

        $errors = array();

        if(empty($title)) {
            $errors[] = dgettext('link', 'Please provide a title.');
        }

        if(empty($href)) {
            $errors[] = dgettext('link', 'Please provide a link address.');
        }

        if(empty($errors)) {
            $link = new Link();
            $link->setKeyId($key_id);
            $link->setTitle($title);
            $link->setHref($href);
            $link->setOther($other);
            $link->save();
        }

        header('HTTP/1.1 303 See Other');
        header('Location: '.$_SERVER['HTTP_REFERER']);
        exit();
    }
}

?>
