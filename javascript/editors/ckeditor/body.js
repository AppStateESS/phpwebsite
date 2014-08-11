<textarea cols="80" id="{ID}" name="{NAME}" rows="10">{VALUE}</textarea>
<script type="text/javascript">

window.onload=function() {
    CKEDITOR.replace('{ID}', 
    {
        on :
          {
             instanceReady : function( ev )
             {
                this.dataProcessor.writer.indentationChars = '  ';

                this.dataProcessor.writer.setRules( 'th',
                   {
                      indent : true,
                      breakBeforeOpen : true,
                      breakAfterOpen : false,
                      breakBeforeClose : false,
                      breakAfterClose : true
                   });
                this.dataProcessor.writer.setRules( 'li',
                   {
                      indent : true,
                      breakBeforeOpen : true,
                      breakAfterOpen : false,
                      breakBeforeClose : false,
                      breakAfterClose : true
                   });
                this.dataProcessor.writer.setRules( 'p',
                   {
                      indent : true,
                      breakBeforeOpen : true,
                      breakAfterOpen : true,
                      breakBeforeClose : true,
                      breakAfterClose : true
                   });
             }
          }   
    });
    CKEDITOR.dtd.$removeEmpty.span = 0;
    CKEDITOR.dtd.$removeEmpty.i = 0;
};

</script>
