<?php

  /**
   * @version $Id$
   * @author Matthew McNaney <mcnaney at gmail dot com>
   */

$default['question']      = _('Change to');
$default['answer']        = '';
$default['value_name']    = 'prompt';
$default['link']          = 'Prompt!';
if (isset($data['type'])) {
    if ($data['type'] == 'button') {
        $bodyfile = $base . 'javascript/prompt/body2.js';
    }
}

?>