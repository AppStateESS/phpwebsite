<?php
/**
 * @version $Id$
 * @author Matthew McNaney <mcnaney at gmail dot com>
 */

class PS_Template {
    public $name = null;
    public $dir  = null;
    public $page_path = null;
    public $data = null;
    public $file = null;

    public $title     = null;
    public $summary   = null;
    public $thumbnail = null;
    public $style     = null;
    public $structure = null;
    public $folders   = null;

    public $error     = null;
    public $page      = null;

    public function __construct($tpl_name)
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

    public function loadTemplate()
    {
                $xml = new Core\XMLParser($this->file);
        $xml->setContentOnly(true);
        if (Core\Error::isError($xml->error)) {
            return $xml->error;
        }

        $result = $xml->format();

        if (empty($result['TEMPLATE'])) {
            return;
        }

        $this->data = $result['TEMPLATE'];
        if (!isset($this->data['TITLE'])) {
            $this->error[] = Core\Error::get(PS_TPL_NO_TITLE, 'pagesmith', 'PS_Template::loadTemplate', $this->name);
            return;
        }

        $this->title     = & $this->data['TITLE'];

        if (isset($this->data['SUMMARY'])) {
            $this->summary   = & $this->data['SUMMARY'];
        }

        if (empty($this->data['THUMBNAIL'])) {
            $this->error[] = Core\Error::get(PS_TPL_NO_TN, 'pagesmith', 'PS_Template::loadTemplate', $this->name);
            return;
        }

        $this->thumbnail = & $this->data['THUMBNAIL'];

        if (isset($this->data['STYLE'])) {
            $this->style     = & $this->data['STYLE'];
        }

        if (empty($this->data['STRUCTURE']['SECTION'])) {
            $this->error[] = Core\Error::get(PS_TPL_NO_SECTIONS, 'pagesmith', 'PS_Template::loadTemplate', $this->name);
            return;
        }

        $this->structure = & $this->data['STRUCTURE']['SECTION'];

        if (isset($this->data['FOLDERS'])) {
            if (is_array($this->data['FOLDERS']['NAME'])) {
                $this->folders = & $this->data['FOLDERS']['NAME'];
            } else {
                $this->folders = array($this->data['FOLDERS']['NAME']);
            }
        }
    }

    public function loadStyle()
    {
        if ($this->style) {
            Layout::addStyle('pagesmith', $this->page_path . $this->style);
        }
    }


    public function pickTpl($parent_page=0)
    {
        $tpl['THUMBNAIL'] = $this->getPickLink($parent_page);
        $tpl['TITLE']     = $this->title;
        $tpl['SUMMARY']   = $this->summary;
        return $tpl;
    }

    public function getPickLink($parent_page=0)
    {
        $vars['aop'] = 'pick_template';
        $vars['tpl'] = $this->name;
        $vars['pid'] = $parent_page;
        return Core\Text::secureLink($this->getThumbnail(), 'pagesmith', $vars);
    }

    public function getThumbnail()
    {
        $tpl_dir = Core\Template::getTemplateHttp('pagesmith');
        return sprintf('<img src="%s%s%s" title="%s" />',
        $tpl_dir, $this->page_path,
        $this->thumbnail, $this->title);
    }
}

?>