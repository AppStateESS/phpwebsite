{START_FORM}
<fieldset>
    <legend><strong>{DOCUMENT_SETTINGS}</strong></legend>
    <p>
    <strong>{BASE_DOC_DIRECTORY_LABEL}</strong><br />
    {BASE_DOC_DIRECTORY}
    <br />
    {FIX_DIRECTORIES}
    </p>
    <p><strong>{MAX_DOCUMENT_SIZE_LABEL}</strong> 
    {MAX_DOCUMENT_SIZE}</p>
    <p><strong>{MAX_PINNED_DOCUMENTS_LABEL}</strong> 
    {MAX_PINNED_DOCUMENTS}</p>
</fieldset>
<fieldset>
    <legend><strong>{IMAGE_SETTINGS}</strong></legend>
    <p>{CAPTION_IMAGES} <strong>{CAPTION_IMAGES_LABEL}</strong></p>
    <p>{POPUP_IMAGE_NAVIGATION} <strong>{POPUP_IMAGE_NAVIGATION_LABEL}</strong></p>
    <p><strong>{MAX_IMAGE_DIMENSION_LABEL}</strong> 
    {MAX_IMAGE_DIMENSION}</p>
    <p><strong>{MAX_IMAGE_SIZE_LABEL}</strong> 
    {MAX_IMAGE_SIZE}</p>
    <p><strong>{MAX_PINNED_IMAGES_LABEL}</strong> 
    {MAX_PINNED_IMAGES}</p>
    <p><strong>{CROP_THRESHOLD_LABEL}</strong> 
    {CROP_THRESHOLD}</p>
    <p>{FORCE_THUMBNAIL_DIMENSIONS} <strong>{FORCE_THUMBNAIL_DIMENSIONS_LABEL}</strong> 
    </p>
    <p><strong>{MAX_THUMBNAIL_SIZE_LABEL}</strong> 
    {MAX_THUMBNAIL_SIZE}</p>
    <fieldset><legend><strong>{CAROUSEL}</strong></legend>
    <p>{JCARO_TYPE_1} <strong>{JCARO_TYPE_1_LABEL}</strong><br />
{JCARO_TYPE_2} <strong>{JCARO_TYPE_2_LABEL}</strong></p>
    <p><strong>{NUMBER_VISIBLE_LABEL}</strong> 
    {NUMBER_VISIBLE}</p>
</fieldset>
</fieldset>

<fieldset>
<legend><strong>{FCK_SETTINGS}</strong></legend>
<p>{FCK_ALL_FOLDERS} <strong>{FCK_ALL_FOLDERS_LABEL}</strong></p>
<p>{FCK_ALLOW_IMAGES} <strong>{FCK_ALLOW_IMAGES_LABEL}</strong></p>
<p>{FCK_ALLOW_DOCUMENTS} <strong>{FCK_ALLOW_DOCUMENTS_LABEL}</strong></p>
<p>{FCK_ALLOW_MEDIA} <strong>{FCK_ALLOW_MEDIA_LABEL}</strong></p>
</fieldset>



<fieldset>
    <legend><strong>{MULTIMEDIA_SETTINGS}</strong></legend>
    <p><strong>{MAX_MULTIMEDIA_SIZE_LABEL}</strong> 
    {MAX_MULTIMEDIA_SIZE}</p>

    <p>{USE_FFMPEG} <strong>{USE_FFMPEG_LABEL}</strong></p>
    
    <p><strong>{FFMPEG_DIRECTORY_LABEL}</strong> 
    {FFMPEG_DIRECTORY}</p>
    
</fieldset>
<!-- BEGIN classify-settings -->
<fieldset>
    <legend><strong>{CLASSIFY_SETTINGS}</strong></legend>
    <p><strong>{CLASSIFY_DIRECTORY_LABEL}</strong><br />
    {CLASSIFY_DIRECTORY}</p>
</fieldset>
<!-- END classify-settings -->
<fieldset>
    <legend><strong>{SYSTEM_SIZE}</strong></legend>
    <p><strong>{SYSTEM_LABEL}</strong> : {MAX_SYSTEM_SIZE}<br />
       <strong>{FORM_LABEL}</strong> : {MAX_FORM_SIZE}<br />
       <strong>{ABSOLUTE_LABEL}</strong> : {ABSOLUTE_SIZE}</p>
</fieldset>
{SUBMIT}
{END_FORM}
