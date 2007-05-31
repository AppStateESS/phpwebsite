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
    <p style="margin-bottom : 1em"><strong>{MAX_IMAGE_WIDTH_LABEL}</strong> 
    {MAX_IMAGE_WIDTH}</p>
    <p style="margin-bottom : 1em"><strong>{MAX_IMAGE_HEIGHT_LABEL}</strong> 
    {MAX_IMAGE_HEIGHT}</p>
    <p style="margin-bottom : 1em"><strong>{MAX_IMAGE_SIZE_LABEL}</strong> 
    {MAX_IMAGE_SIZE}</p>
    <p style="margin-bottom : 1em"><strong>{MAX_PINNED_IMAGES_LABEL}</strong> 
    {MAX_PINNED_IMAGES}</p>
</fieldset>
<fieldset>
    <legend>{MULTIMEDIA_SETTINGS}</legend>
    <p style="margin-bottom : 1em"><strong>{MAX_MULTIMEDIA_SIZE_LABEL}</strong> 
    {MAX_MULTIMEDIA_SIZE}</p>

    <p style="margin-bottom : 1em">{USE_FFMPEG} <strong>{USE_FFMPEG_LABEL}</strong></p>
    
    <p style="margin-bottom : 1em"><strong>{FFMPEG_DIRECTORY_LABEL}</strong> 
    {FFMPEG_DIRECTORY}</p>
    
</fieldset>
{SUBMIT}
{END_FORM}
