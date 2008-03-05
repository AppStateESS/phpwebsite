<?php
  /**
   * @version $Id$
   * @author Matthew McNaney <mcnaney at gmail dot com>
   */

class PS_Template {
    var $name = null;
    var $dir  = null;
    var $page_path = null;
    var $data = null;
    var $file = null;

    var $title     = null;
    var $summary   = null;
    var $thumbnail = null;
    var $style     = null;
    var $structure = null;
    var $folders   = null;

    var $error     = null;
    var $page      = null;

    function PS_Template($tpl_name)
    {
        $this->name = $tpl_name;
        $this->dir  = PageSmith::pageTplDir() . $this->name;
        $this->file = $this->dir . '/structure.xml';
        $this->page_path = sprintf('page_templates/%s/', $this->name);
        if (!is_file($this->file)) {
            return;
        }

        $this->loadTemplate($tpl_name);
    }

    function loadTemplate()
    {
        PHPWS_Core::initCoreClass('XMLParser.php');
        $xml = new XMLParser($this->file);
        $xml->setContentOnly(true);
        if (PHPWS_Error::isError($xml->error)) {
            return $xml->error;
        }

        $result = $xml->format();

        if (empty($result['TEMPLATE'])) {
            return;
        }

        $this->data = $result['TEMPLATE'];
        if (!isset($this->data['TITLE'])) {
            $this->error[] = PHPWS_Error::get(PS_TPL_NO_TITLE, 'pagesmith', 'PS_Template::loadTemplate', $this->name);
            return;
        }

        $this->title     = & $this->data['TITLE'];

        if (isset($this->data['SUMMARY'])) {
            $this->summary   = & $this->data['SUMMARY'];
        }

        if (empty($this->data['THUMBNAIL'])) {
            $this->error[] = PHPWS_Error::get(PS_TPL_NO_TN, 'pagesmith', 'PS_Template::loadTemplate', $this->name);
            return;
        }

        $this->thumbnail = & $this->data['THUMBNAIL'];
        
        if (isset($this->data['STYLE'])) {
            $this->style     = & $this->data['STYLE'];
        }

        if (empty($this->data['STRUCTURE']['SECTION'])) {
            $this->error[] = PHPWS_Error::get(PS_TPL_NO_SECTIONS, 'pagesmith', 'PS_Template::loadTemplate', $this->name);
            return;
        }

        $this->structure = & $this->data['STRUCTURE']['SECTION'];

        if (isset($this->data['FOLDERS'])) {
            $this->folders = & $this->data['FOLDERS']['NAME'];
        }
    }

    function loadStyle()
    {
        if ($this->style) {
            Layout::addStyle('pagesmith', $this->page_path . $this->style);
        }
    }


    function pickTpl()
    {
        $tpl['THUMBNAIL'] = $this->getPickLink();
        $tpl['TITLE']     = $this->title;
        $tpl['SUMMARY']   = $this->summary;
        return $tpl;
    }

    function getPickLink()
    {
        $vars['aop'] = 'pick_template';
        $vars['tpl'] = $this->name;
        return PHPWS_Text::secureLink($this->getThumbnail(), 'pagesmith', $vars);
    }

    function getThumbnail()
    {
        return sprintf('<img src="templates/pagesmith/%s%s" title="%s" />',
                       $this->page_path, $this->thumbnail, $this->title);
    }
}

?>