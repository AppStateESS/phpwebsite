<?php

function layout_install(&$content, $branchInstall=FALSE)
{
  $page_title = NULL;

  if ($_POST['process_layout']) {
    if (empty($_POST['page_title'])) {
      $error  = _('Please enter a page title.');
    } else {
      $page_title = strip_tags($_POST['page_title']);
      $default_theme = $_POST['theme'];
    }

    if (!isset($error)) {
      $db = & new PHPWS_DB('layout_config');
      $db->addValue('default_theme', trim($default_theme));
      $db->addValue('page_title', $page_title);
      $db->update();
      $content[] = _('Layout settings updated.');
      return TRUE;
    } else {
      $tpl['ERROR'] = $error;
    }
  } else {
    $page_title = 'My phpWebSite';
    $default_theme = 'zen';
  }

  PHPWS_Core::initCoreClass('File.php');
  $theme_dir = PHPWS_SOURCE_DIR . 'themes/';
  $available_themes = PHPWS_File::readDirectory($theme_dir, TRUE);

  $form = & new PHPWS_Form;
  $form->mergeTemplate($tpl);
  $form->addHidden('step', 3);
  $form->addHidden('module', 'layout');
  $form->addHidden('process_layout', 1);

  if (empty($available_themes)) {
    $content[] = _('No themes installed.');
    $content[] = _('Expect an error theme when finished.');
  } else {
    $form->addSelect('theme', $available_themes);
    $form->reindexValue('theme');
    $form->setLabel('theme', _('Pick a theme'));
    $form->setMatch('theme', $default_theme);
  }

  $form->addText('page_title', $page_title);
  $form->setLabel('page_title', _('Page Title'));
  $form->setTitle('page_title', _('Page Title: Name of your web site'));
  $form->addSubmit(_('Done'));
  
  $template = $form->getTemplate();
  $content[] = PHPWS_Template::process($template, 'layout', 'setup.tpl');

  return FALSE;

}


?>