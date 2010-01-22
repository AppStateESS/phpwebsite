<?php

/**
 * Description
 * @author Jeff Tickle <jtickle at tux dot appstate dot edu>
 */

abstract class LinkCommand
{
    abstract function getRequestVars();
    abstract function execute(LinkContext $context);

    function initForm(PHPWS_Form &$form)
    {   
        $form->addHidden('module', 'link');
        foreach($this->getRequestVars() as $key=>$val) {
            $form->addHidden($key, $val);
        }   
    }   
    
    function getURI(){
        $uri = $_SERVER['SCRIPT_NAME'] . "?module=link";
        foreach($this->getRequestVars() as $key=>$val) {
            $uri .= "&$key=$val";
        }   
    
        return $uri;
    }   
    
    function getLink($text)
    {
        return PHPWS_Text::moduleLink(dgettext('link', $text),
            'link', $this->getRequestVars());
    }   
    
    function redirect()
    {   
        $path = $this->getURI();
    
        header('HTTP/1.1 303 See Other');
        header("Location: $path");
        exit();
    }   
}

?>
