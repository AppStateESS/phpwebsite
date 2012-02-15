<script type="text/javascript">
//<![CDATA[
    $(function() {
        var isPrevented = false;
        
        handleNavigate = function() {
            return "Your changes have not been saved.  Are you sure you want to leave this page?";
        }
        
        doPreventNavigate = function() {
            if(isPrevented) return;
            
            $(window).on('beforeunload', handleNavigate);
            isPrevented = true;
        };

        undoPreventNavigate = function() {
            if(!isPrevented) return;
            
            $(window).off('beforeunload', handleNavigate);
            isPrevented = false;
        };
        
        $('.form-protected').each(function() {
            $(this).find('input').change(function() {
                doPreventNavigate();
            });
            $(this).submit(function() {
                undoPreventNavigate();
            });
            $(this).bind('reset', function() {
                undoPreventNavigate();
            });
        });
    });
//]]>
</script>
