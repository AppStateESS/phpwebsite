{START_FORM}
<fieldset>
    <legend>{DOCUMENT_SETTINGS}</legend>
    <p style="margin-bottom : 1em">
    <strong>{BASE_DOC_DIRECTORY_LABEL}</strong><br />
    {BASE_DOC_DIRECTORY}
    </p>
    <p style="margin-bottom : 1em"><strong>{MAX_DOCUMENT_SIZE_LABEL}</strong> 
    {MAX_DOCUMENT_SIZE}</p>
    <p style="margin-bottom : 1em"><strong>{MAX_PINNED_DOCUMENTS_LABEL}</strong> 
    {MAX_PINNED_DOCUMENTS}</p>
</fieldset>
<fieldset>
    <legend>{IMAGE_SETTINGS}</legend>
    <p style="margin-bottom : 1em">{CAPTION_IMAGES} <strong>{CAPTION_IMAGES_LABEL}</strong></p>
    <p style="margin-bottom : 1em"><strong>{MAX_IMAGE_DIMENSION_LABEL}</strong> 
    {MAX_IMAGE_DIMENSION}</p>
    <p style="margin-bottom : 1em"><strong>{MAX_IMAGE_SIZE_LABEL}</strong> 
    {MAX_IMAGE_SIZE}</p>
    <p style="margin-bottom : 1em"><strong>{MAX_PINNED_IMAGES_LABEL}</strong> 
    {MAX_PINNED_IMAGES}</p>
    <p style="margin-bottom : 1em"><strong>{CROP_THRESHOLD_LABEL}</strong> 
    {CROP_THRESHOLD}</p>
</fieldset>
<fieldset>
    <legend>{MULTIMEDIA_SETTINGS}</legend>
    <p style="margin-bottom : 1em"><strong>{MAX_MULTIMEDIA_SIZE_LABEL}</strong> 
    {MAX_MULTIMEDIA_SIZE}</p>

    <p style="margin-bottom : 1em">{USE_FFMPEG} <strong>{USE_FFMPEG_LABEL}</strong></p>
    
    <p style="margin-bottom : 1em"><strong>{FFMPEG_DIRECTORY_LABEL}</strong> 
    {FFMPEG_DIRECTORY}</p>
    
</fieldset>
<!-- BEGIN classify-settings -->
<fieldset>
    <legend>{CLASSIFY_SETTINGS}</legend>
    <p style="margin-bottom : 1em"><strong>{CLASSIFY_DIRECTORY_LABEL}</strong><br />
    {CLASSIFY_DIRECTORY}</p>
</fieldset>
<!-- END classify-settings -->
<fieldset>
    <legend>{SYSTEM_SIZE}</legend>
    <p><strong>{SYSTEM_LABEL}</strong> : {MAX_SYSTEM_SIZE}<br />
       <strong>{FORM_LABEL}</strong> : {MAX_FORM_SIZE}<br />
       <strong>{ABSOLUTE_LABEL}</strong> : {ABSOLUTE_SIZE}</p>
</fieldset>
{SUBMIT}
{END_FORM}
