<?php

class Version_Approval {
  var $version_id  = 0;
  var $module      = NULL;
  var $vr_table       = NULL;
  var $info        = NULL;
  var $view_url    = NULL;
  var $edit_url    = NULL;
  var $approve_url = NULL;
  var $refuse_url  = NULL;


  function setVersionId($version_id)
  {
    $this->version_id = (int)$version_id;
  }
  
  function setModule($module)
  {
    $this->module = $module;
  }

  function getModule()
  {
    return $this->module;
  }

  function setTable($table)
  {
    $this->vr_table = $table;
  }

  function setInfo($info)
  {
    $this->info = $info;
  }

  function getInfo()
  {
    return $this->info;
  }

  function setViewUrl($view_url)
  {
    $this->view_url = $view_url;
  }

  function setEditUrl($edit_url)
  {
    $this->edit_url = $edit_url;
  }

  function setApproveUrl($approve_url)
  {
    $this->approve_url = $approve_url;
  }

  function setRefuseUrl($refuse_url)
  {
    $this->refuse_url = $refuse_url;
  }

  function save()
  {
    $db = & new PHPWS_DB('version_approval');
    $db->saveObject($this);
  }
  
}

?>
