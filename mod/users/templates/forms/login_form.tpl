{START_FORM}

    <div class="control-group">
        {PHPWS_USERNAME_LABEL}
        {PHPWS_USERNAME}
    </div>
    
    <div class="control-group">        
        {PHPWS_PASSWORD_LABEL}
        {PHPWS_PASSWORD}
    </div>
        
    <!-- BEGIN graphic-confirm -->
        {CONFIRM_GRAPHIC_LABEL}
        {GRAPHIC}
        {CONFIRM_GRAPHIC} {CONFIRM_INSTRUCTIONS}
        
        <!-- BEGIN graphic-error -->
        <div class="error">{CONFIRM_ERROR}</div>
        <!-- END graphic-error -->
        
    <!-- END graphic-confirm -->


<div class="control-group">
    <button type="submit" name="{SUBMIT_NAME}" id="{SUBMIT_ID}" class="{SUBMIT_CLASS} btn btn-primary">{SUBMIT_VALUE}</button>
</div>
{END_FORM}