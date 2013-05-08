<style>
/*
 * User popup styles 
 */
ac_results {
	padding: 0px;
	border: 1px solid WindowFrame;
	background-color: Window;
	overflow: hidden;
}

.ac_results ul {
	width: 150px;
	list-style-position: outside;
	list-style: none;
	padding: 0;
	margin: 0;
}

.ac_results iframe {
	display:none;/*sorry for IE5*/
	display/**/:block;/*sorry for IE5*/
	position:absolute;
	top:0;
	left:0;
	z-index:-1;
	filter:mask();
	width:3000px;
	height:3000px;
}

.ac_results li {
	margin: 0px;
	padding: 2px 5px;
	cursor: pointer;
	display: block;
	width: 100%;
	font: menu;
	font-size: 12px;
	overflow: hidden;
        background-color : white;
        border : 1px solid gray;
        border-top : none;
}

.ac_loading {
	background : Window url('./indicator.gif') right center no-repeat;
}

.ac_over {
	background-color: Highlight;
	color: HighlightText;
}

</style>
<script type="text/javascript" src="javascript/jquery/jquery.autocomplete.js"></script>
<script type="text/javascript">
$(document).ready(function() {
    $("#edit-staff_username").autocomplete(
                                          "index.php",
            {
                extraParams:{module:'checkin',aop:'search_users'},
                delay:10,
                minChars:2,
                matchSubset:1,
                matchContains:1,
                cacheLength:10,
                autoFill:true
            }
                                          );
});


</script>
