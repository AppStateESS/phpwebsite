<?php

class FC_Image extends PHPWS_Image {
    function getRowTags()
    {
        $vars['image_id'] = $this->getId();
        $vars['action'] = 'editImage';
        $links[] = PHPWS_Text::secureLink(_('Edit'), 'filecabinet', $vars);
        $vars['action'] = 'deleteImage';
        $links[] = PHPWS_Text::secureLink(_('Delete'), 'filecabinet', $vars);
        $vars['action'] = 'copyImage';
        $links[] = PHPWS_Text::moduleLink(_('Copy'), 'filecabinet', $vars);

        return implode(' | ', $links);
    }
}
?>