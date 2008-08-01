<?php
  /**
   * @version $Id$
   * @author Matthew McNaney <mcnaney at gmail dot com>
   */

PHPWS_Core::initModClass('pagesmith', 'PS_Section.php');

class PS_Block extends PS_Section {
    // Id to the element tracked by this block e.g. the image id
    public $type_id = 0;
    public $width   = 0;
    public $height  = 0;

    public function __construct($id=0)
    {
        if (!$id) {
            return;
        }

        $this->id = (int)$id;
        $this->init();
    }

    public function init()
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

    public function loadFiller()
    {
        PHPWS_Core::initModClass('filecabinet', 'Cabinet.php');
        $manager = Cabinet::fileManager($this->secname, $this->type_id);
        $manager->maxImageWidth($this->width);
        $manager->maxImageHeight($this->height);
        switch ($this->sectype) {
        case 'image':
            $manager->imageOnly();
            break;

        case 'document':
            $manager->documentOnly();
            break;

        case 'media':
            $manager->mediaOnly();
            break;

        default:
        }

        $this->content = $manager->get();
    }

    public function loadSaved()
    {
        $this->loadFiller();
        return true;
    }

    public function getContent()
    {
        if (empty($this->content)) {
                PHPWS_Core::initModClass('filecabinet', 'Cabinet.php');
                $this->content = Cabinet::getTag($this->type_id);
        }
        return $this->content;
    }

    public function save($key_id=null)
    {
        $db = new PHPWS_DB('ps_block');
        $db->saveObject($this);
    }

}

?>