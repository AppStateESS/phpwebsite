<?php

/**
 * Description
 * @author Jeff Tickle <jtickle at tux dot appstate dot edu>
 */

abstract class PollCommand
{
    abstract function getRequestVars();
    abstract function execute(PollContext $context);

    function initForm(PHPWS_Form &$form)
    {   
        $form->addHidden('module', 'poll');
        foreach($this->getRequestVars() as $key=>$val) {
            $form->addHidden($key, $val);
        }   
    }   
    
    function getURI(){
        $uri = $_SERVER['SCRIPT_NAME'] . "?module=poll";
        foreach($this->getRequestVars() as $key=>$val) {
            $uri .= "&$key=$val";
        }   
    
        return $uri;
    }   
    
    function getLink($text)
    {
        return PHPWS_Text::moduleLink(dgettext('poll', $text),
            'poll', $this->getRequestVars());
    }   
    
    function redirect()
    {   
        $path = $this->getURI();
        NQ::close();
    
        header('HTTP/1.1 303 See Other');
        header("Location: $path");
        SDR::quit();
    }   
}

?>
