<?php

class CrutchForm {
  function makeForm($name, $action, $elements, $method='post', $breaks=FALSE, $file=FALSE) {
    if($file) {
      $form[0] = "<form name=\"$name\" action=\"$action\" method=\"$method\" enctype=\"multipart/form-data\">\n";
    } else {
      $form[0] = '<form name="' . $name . '" action="' . $action . '" method="' 
	. $method . '">' . "\n";
    }
    
    if($breaks) {
      $form[1] = implode("<br />\n", $elements);
    } else {
      $form[1] = implode("\n", $elements);
    }

    $form[2] = "</form>\n";
    return implode('', $form);
  }

  function formTextField($name, $value, $size=NULL, $maxsize=NULL, $label=NULL){
    $form = & new Form_TextField($name, $value);

    if (isset($size))
      $form->setSize($size);

    if (isset($maxsize))
      $form->setMaxSize($maxsize);

    return $label . ' ' . $form->get();
  }

  function formTextArea ($name, $value=NULL, $rows=5, $cols=40, $label=NULL){
    $form = & new Form_TextArea($name, $value);

    $form->setRows($rows);
    $form->setCols($cols);

    return $label . $form->get();
  }

  function formFile($name){
    $form = & new Form_File($name);
    return $form->get();
  }

  
  function formSelect($name, $opt_array, $match = NULL, $ignore_index = FALSE, $match_to_value = FALSE, $onchange = NULL, $label = NULL)
  {

    $form = & new Form_Select($name);
    $form->setName($name);

    if ($ignore_index) {
      foreach ($opt_array as $opt_value) {
	$new_vars[$opt_value] = $opt_value;
      }
      $opt_array = $new_vars;
    }

    $form->setValue($opt_array);

    if (isset($match)) {
      $form->setMatch($match);
    }

    return $form->get();
  }
  
  function formRadio($name, $value, $match=NULL, $match_diff=NULL, $label=NULL) {
    $form = & new Form_RadioButton($name, $value);
    if (isset($match_diff) && $match == $match_diff)
      $form->setMatch();
    elseif ($match == $value)
      $form->setMatch();

    if (isset($label))
      return $form->get() . ' ' . $label;
    else
      return $form->get();
	
  }

  function formCheck($name, $value, $match=NULL, $match_diff=NULL, $label=NULL) {
    $form = & new Form_CheckBox($name, $value);
    if (isset($match_diff) && $match == $match_diff)
      $form->setMatch();
    elseif ($match == $value)
      $form->setMatch();

    if (isset($label))
      return $form->get() . ' ' . $label;
    else
      return $form->get();
  }


  function formSubmit($value, $name=NULL, $class=NULL) {
    $form = & new Form_Submit($name, $value);
    if (isset($class))
      $form->setClass($class);
    
    return $form->get();
  }

  function formHidden($name, $value=NULL) {
    $form = & new Form_Hidden($name, $value);
    return $form->get();
  }

  function formDate($date_name, $date_match=NULL, $yearStart=NULL, $yearEnd=NULL, $useBlanks=FALSE){
    $form = & new Form_File($date_name);
    if (!isset($date_match) && !isset($useBlanks))
      $date_match = date('Ymd');
    elseif(!$date_match && $useBlanks)
      $date_match = '        ';

    $y_match = substr($date_match, 0, 4);
    $m_match = substr($date_match, 4, 2);
    $d_match = substr($date_match, 6, 2);

    for ($i=1; $i<13; $i++){
      $date = strftime(PREFERRED_MONTH_DISPLAY, mktime(2,0,0,$i,1,2000));
      $month[$i] = $date;
    }

    for ($i=1; $i<32; $i++){
      $date = strftime(PREFERRED_DAY_DISPLAY, mktime(0,0,0,1,$i));
      $day[$i] = $date;
    }

    if (is_numeric($yearStart) && is_numeric($yearEnd))
      $length = $yearEnd - $yearStart;
    elseif (is_numeric($yearStart) && $yearStart < date('Y'))
      $length = 10;
    elseif (($yearStart - (int)date('Y')) > 10){
      $length = $yearStart - (int)date('Y') + 3;
      $yearStart = (int)date('Y');
    }


    if (!$yearStart)
      $yearStart = date('Y', mktime());

    if (!isset($length) || $length < 0 || $length > 1000)
      $length = 10;

    for ($i=$yearStart; $i<=$yearStart+$length; $i++){
      $date = strftime(PREFERRED_YEAR_DISPLAY, mktime(0,0,0,1,1,$i));
      $year[$i] = $date;
    }

    if($useBlanks) {
      $day[''] = '';
      asort($day);
      reset($day);
      $month[''] = '';
      asort($month);
      reset($month);
      $year[''] = '';
      asort($year);
      reset($year);
    }

    $form = & new PHPWS_Form;
    $form->add($date_name . '_month', 'select', $month);
    $form->setMatch($date_name . '_month', $m_match);
    $form->add($date_name . '_day', 'select', $day);
    $form->setMatch($date_name . '_day', $d_match);
    $form->add($date_name . '_year', 'select', $year);
    $form->setMatch($date_name . '_year', $y_match);

    $dateOrder[strpos(PHPWS_DATE_ORDER, 'm')] = $form->get($date_name . '_month');
    $dateOrder[strpos(PHPWS_DATE_ORDER, 'd')] = $form->get($date_name . '_day');
    $dateOrder[strpos(PHPWS_DATE_ORDER, 'y')] = $form->get($date_name . '_year');
    return sprintf('%s %s %s', $dateOrder[0], $dateOrder[1], $dateOrder[2]);
  }

}


?>