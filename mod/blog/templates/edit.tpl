<script type="text/javascript">
function toggleUrl(select) {
    url = document.getElementById('image-url');
    textbox = document.getElementById('edit-blog_image_url');

    if (select.value == 'url') {
        url.style.opacity = '1';
        textbox.disabled = false;
    } else {
        url.style.opacity = '.5';
        textbox.disabled = true;
    }
}
</script>

<!-- BEGIN error --><span class="error">{MESSAGE}</span><!-- END error -->
{START_FORM}
<div class="top-label">
    <p>{TITLE_LABEL}<br />{TITLE}</p>
    <p>{SUMMARY_LABEL}<br />{SUMMARY}</p>
<!-- BEGIN image -->
    <div>{FILE_MANAGER}</div>
    <p>{THUMBNAIL} {THUMBNAIL_LABEL}</p>
    <p>{IMAGE_LINK_LABEL}<br />{IMAGE_LINK}</p>
    <p id="image-url" style="opacity : {OP}">{IMAGE_URL_LABEL}<br />{IMAGE_URL}</p>
<!-- END image -->
    <p>{ENTRY_LABEL}<br />{ENTRY}</p>
    <p>
        {PUBLISH_DATE_LABEL} <span class="smaller">{EXAMPLE}</span><br />    
        {PUBLISH_DATE}
    </p>
    <p>
        {EXPIRE_DATE_LABEL} <span class="smaller">{EXAMPLE}</span><br />    
        {EXPIRE_DATE}
    </p>
<!-- BEGIN comments -->
    <p>
        {ALLOW_COMMENTS} {ALLOW_COMMENTS_LABEL}<br />
        {ALLOW_ANON} {ALLOW_ANON_LABEL}
    </p>
<!-- END comments -->
    <!-- BEGIN captcha -->
    <p>
    {CAPTCHA_IMAGE}<br />
    {CAPTCHA} {CAPTCHA_LABEL}
    </p>
    <!-- END captcha -->

</div>
{SUBMIT}
{END_FORM}
