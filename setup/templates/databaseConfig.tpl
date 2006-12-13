{START_FORM}
<div id="config-form">
  <!-- BEGIN main --><p>{MAIN}</p><!-- END main -->
  <div class="config-item">
    <div class="label">{DBNAME_LBL}</div>
    <div class="definition">{DBNAME_DEF}</div>
    <div class="form-item">{DBNAME}</div>
     <!-- BEGIN dbname --><div class="error">{DBNAME_ERR}</div><!-- END dbname -->
  </div>

  <div class="config-item">
    <div class="label">{DBTYPE_LBL}</div>
    <div class="definition">{DBTYPE_DEF}</div>
    <div class="form-item">{DBTYPE}</div>
  </div>

  <div class="config-item">
    <div class="label">{DBUSER_LBL}</div>
    <div class="definition">{DBUSER_DEF}</div>
    <div class="form-item">{DBUSER}</div>
    <!-- BEGIN dbuser --><div class="error">{DBUSER_ERR}</div><!-- END dbuser -->
  </div>

  <div class="config-item">
    <div class="label">{DBPASS_LBL}</div>
    <div class="definition">{DBPASS_DEF}</div>
    <div class="form-item">{DBPASS}</div>
    <!-- BEGIN dbpass --><div class="error">{DBPASS_ERR}</div><!-- END dbpass -->
  </div>

  <div class="config-item">
    <div class="label">{DBPREF_LBL}</div>
    <div class="definition">{DBPREF_DEF}</div>
    <div class="form-item">{DBPREFIX}</div>
    <!-- BEGIN dbpref --><div class="error">{DBPREF_ERR}</div><!-- END dbpref -->
  </div>

  <div class="config-item">
    <div class="label">{DBHOST_LBL}</div>
    <div class="definition">{DBHOST_DEF}</div>
    <div class="form-item">{DBHOST}</div>
  </div>

  <div class="config-item">
    <div class="label">{DBPORT_LBL}</div>
    <div class="definition">{DBPORT_DEF}</div>
    <div class="form-item">{DBPORT}</div>
  </div>
 {DEFAULT_SUBMIT}
</div>

{END_FORM}
