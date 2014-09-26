<?php
namespace calendar;
/**
 *
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @license http://opensource.org/licenses/lgpl-3.0.html
 */
class Modal {

    private $id;
    private $content;
    private $title;

    /**
     *
     * @var array
     */
    private $button;

    public function __construct($id, $content, $title = null)
    {
        $this->id = $id;
        $this->content = $content;
        $this->title = $title;
    }

    public function addButton($button)
    {
        $this->button[] = $button;
    }

    public function __toString()
    {
        $tpl['id'] = $this->id;
        $tpl['content'] = $this->content;
        if (!empty($this->title)) {
            $tpl['title'] = $this->title;
        }

        if ($this->button) {
           $tpl['button'] = implode("\n", $this->button);
        }

        $template = new \Template($tpl);
        $template->setModuleTemplate('calendar', 'admin/modal.html');
        return $template->render();
    }

}

?>
