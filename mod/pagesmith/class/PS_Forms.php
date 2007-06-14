<?php
  /**
   * @version $Id$
   * @author Matthew McNaney <mcnaney at gmail dot com>
   */

class PS_Forms {
    var $template = null;
    var $ps       = null;
    var $tpl_list = null;
    

    function editPage()
    {
        if (!$this->ps->page->id) {
            if (isset($_REQUEST['tpl'])) {
                $this->pageLayout();
            } else {
                $this->pickTemplate();
            }
            return;
        }

    }


    function loadTemplates()
    {
        $tpl_dir = $this->ps->pageTplDir();
        $templates = PHPWS_File::listDirectories($tpl_dir);

        if (empty($templates)) {
            PHPWS_Error::log(PS_TPL_DIR, 'pagesmith', 'PS_Forms::loadTemplates', $tpl_dir);
        }

        foreach ($templates as $tpl) {
            $pg_tpl = $this->ps->getPageTemplate($tpl);

            if ($pg_tpl) {
                $this->tpl_list[$tpl] = & $pg_tpl;
            }
        }

    }

    function pageLayout()
    {

    }

    function pageList()
    {

    }

    function pickTemplate()
    {
        $this->loadTemplates();
    }

}

?>