<h2>{TITLE}</h2>
<!--  BEGIN main --><p id="main-text">{MAIN}</p><!-- END main -->
{START_FORM}
<p>{DBTYPE_LABEL} {DBTYPE}<br />
{DBTYPE_DEF}</p>
<p>{DBNAME_LABEL} {DBNAME} <span class="error">{DBNAME_ERR}</span><br />
{DBNAME_DEF}</p>
<p>{DBUSER_LABEL} {DBUSER} <span class="error">{DBUSER_ERR}</span><br />
{DBUSER_DEF}</p>
<p>{DBPASS_LABEL} {DBPASS} <span class="error">{DBPASS_ERR}</span><br />
{DBPASS_DEF}</p>
<p>{DBPREFIX_LABEL} {DBPREFIX} <span class="error">{DBPREF_ERR}</span><br />
{DBPREF_DEF}</p>
<p>{DBPORT_LABEL} {DBPORT} <span class="error">{DBPORT_ERR}</span><br />
{DBPORT_DEF}</p>
<p>{DBHOST_LABEL} {DBHOST} <span class="error">{DBHOST_ERR}</span><br />
{DBHOST_DEF}</p>
{DEFAULT_SUBMIT}
{END_FORM}
