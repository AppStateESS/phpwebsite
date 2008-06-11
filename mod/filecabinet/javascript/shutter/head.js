<script type="text/javascript">
//<![CDATA[
$(document).ready(function() {

check = $('#settings_use_jcarousel').attr('checked');

if (!check) {
   $('#jcarousel').hide();
}

$('#settings_use_jcarousel').change(function() {
$('#jcarousel').slideToggle('fast');
});
});
//]]>
</script>