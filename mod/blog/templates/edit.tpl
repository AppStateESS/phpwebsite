<!-- BEGIN error -->
<div class="alert alert-danger"><button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>{MESSAGE}</div>
<!-- END error -->
<form method="post" class="form-inline" action="index.php" id="edit-blog" autocomplete="on">
{HIDDEN_FIELDS}
<div class="top-label">
    <p>{TITLE_LABEL}<br />
        {TITLE}</p>
    <p>{SUMMARY_LABEL}<br /><span style="font-style : italic; font-size : 90%">({REMINDER})</span><br />
        {SUMMARY}</p>
    <div>
        <p>{PUBLISH_DATE_LABEL} <small>{EXAMPLE}</small><br />
            {PUBLISH_DATE}</p>
        <p>{EXPIRE_DATE_LABEL} <small>{EXAMPLE}</small><br />
            {EXPIRE_DATE}</p>
    </div>
    <!-- BEGIN captcha --><p>{CAPTCHA_IMAGE}<br />{CAPTCHA} {CAPTCHA_LABEL}<!-- END captcha -->
</div>
<div style="text-align : center; clear : both; padding-top : 5px; border-top : 1px solid black"><button class="btn btn-success" onclick="this.form.submit()"><i class="fa fa-save"></i> Save entry</button></div>
</form>
