<style>
a.popup {
    position : relative;
}

a.popup span.default-pop {
    display : block;
    border : 1px solid black;
    position : absolute;
    background-color : white;
    width : 200px;
    left : 0px;
}
</style>

<script type="text/javascript">
$(document).ready(function() {
	$('a.popup span').hide();
	$('a.popup').hover(
		function () {
			$(this).children('span').show();
		},
		function () {
			$(this).children('span').hide();
		}
	);
	
});
</script>