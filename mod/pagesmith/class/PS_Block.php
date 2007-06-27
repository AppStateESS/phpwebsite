<?php
  /**
   * @version $Id$
   * @author Matthew McNaney <mcnaney at gmail dot com>
   */

PHPWS_Core::initModClass('pagesmith', 'PS_Section.php');

class PS_Block extends PS_Section {
    var $btype   = null;
    var $type_id = 0;
    var $width   = 0;
    var $height  = 0;

    function PS_Block($id=0)
    {
        if (!$id) {
            return;
        }

        $this->id = (int)$id;
        $this->init();
    }

    function init()
    {
        $db = new PHPWS_DB('ps_block');
        $result = $db->loadObject($this);
        if (PHPWS_Error::logIfError($result)) {
            return $result;
        }
        if (!$result) {
            $this->id = 0;
            return false;
        } else {
            return true;
        }
    }

    function loadContent($form_mode=false)
    {
        if ($form_mode) {
            PHPWS_Core::initModClass('filecabinet', 'Cabinet.php');
            $manager = Cabinet::imageManager($this->type_id, $this->secname, $this->width, $this->height, false);
            $this->content = $manager->get();
        } else {
            switch ($this->btype) {
            case 'image':
                PHPWS_Core::initModClass('filecabinet', 'Image.php');
                $image = new PHPWS_Image($this->type_id);
                $this->content = $image->getTag();
            }
        }
    }


    function getContent()
    {
        return $this->content;
    }


    function save()
    {
        $db = new PHPWS_DB('ps_block');
        $db->saveObject($this);
    }

}

?>