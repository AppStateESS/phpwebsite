<script type="text/javascript">
//<![CDATA[

    function subtractQty(field){
        if(document.getElementById(field).value - 1 < 0)
            return;
        else
             document.getElementById(field).value--;
    }

    function confirmAction(question, target) {
        if (question) {
            question = question;
        } else {
            var question = '{confirm_question}';
        }
        if (confirm(question)) {
            target.submit();
        } else {
            return false;

        }
    }

//]]>
</script>