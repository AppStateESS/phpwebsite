<?php
/**
    * @version $Id$
    * @author Verdon Vaillancourt <verdonv at gmail dot com>
 */

$data['submit_label'] = dgettext('elections', 'Vote');
$data['min'] = 1;
$data['max'] = 1;
$data['qty'] = null;
$data['type'] = 'check';

$data['text_min'] = dgettext('elections', 'You must make at least ');
$data['text_min_2'] = dgettext('elections', ' choice(s).');
$data['text_max'] = dgettext('elections', 'You have made too many choices and must reduce your number by ');
$data['text_max_2'] = dgettext('elections', ' to proceed.');
$data['text_ok'] = dgettext('elections', 'You have cast ');
$data['text_ok_2'] = dgettext('elections', ' of ');
$data['text_ok_3'] = dgettext('elections', ' possible votes. Click OK to post your vote(s) or cancel to make a change.');

?>