<?php
class Blog_Form {
  function edit(&$blog, $version_id=NULL)
  {
    PHPWS_Core::initCoreClass('Editor.php');
    PHPWS_Core::initModClass('categories', 'Category_Item.php');
    $form = & new PHPWS_Form;
    $form->addHidden('module', 'blog');
    $form->addHidden('action', 'admin');
    $form->addHidden('command', 'postEntry');

    $cat_item = & new Category_Item('blog');
    $cat_item->setItemId($blog->id);
    
    if (isset($version_id)) {
      $form->addHidden('version_id', $version_id);
      $cat_item->setVersionId($version_id);
      if (Current_User::isUnrestricted('blog')) {
	$form->addSubmit('approve_entry', _('Save Changes and Approve'));
      }
    }

    if (isset($blog->id) || isset($version_id)){
      $form->addHidden('blog_id', $blog->id);
      $form->addSubmit('submit', _('Update Entry'));
    } else
      $form->addSubmit('submit', _('Add Entry'));

    $form->addTextArea('entry', $blog->getEntry());
    $form->useEditor('entry');
    $form->setRows('entry', '10');
    $form->setWidth('entry', '80%');
    $form->setLabel('entry', _('Entry'));

    $form->addText('title', $blog->title);
    $form->setSize('title', 40);
    $form->setLabel('title', _('Title'));

    if (Current_User::isUnrestricted('blog') && empty($version_id)) {
      $viewable_opts[] = 0;
      $viewable_opts[] = 1;
      $viewable_opts[] = 2;
      $viewable_label[] = _('Unrestricted');
      $viewable_label[] = _('Only logged in users');
      $viewable_label[] = _('Only certain groups');
      
      $form->addRadio('viewable', $viewable_opts);
      $form->setLabel('viewable', $viewable_label);
      $form->setMatch('viewable', $blog->getRestricted());
      $form->addTplTag('VIEWABLE_MAIN_LABEL', _('Viewing Permissions'));
    }

    $template = $form->getTemplate();

    $template['CATEGORIES_LABEL']    = _('Category');
    $template['CATEGORIES']          = $cat_item->getForm();


    if (Current_User::isUnrestricted('blog') && empty($version_id)){
      $assign = PHPWS_User::assignPermissions('blog', $blog->getId());
      $template = array_merge($assign, $template);
    }

    return PHPWS_Template::process($template, 'blog', 'edit.tpl');
  }
}
?>