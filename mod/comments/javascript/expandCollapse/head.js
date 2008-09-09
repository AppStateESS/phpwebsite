<script type="text/javascript">

/***********************************************
* Used for expanding and collapsing block elements.
* Used with permission from "My Favorite Javascripts for Designers"
* Visit http://www.blakems.com/archives/000087.html
***********************************************/

function expandCollapse() {
	for (var i=0; i<expandCollapse.arguments.length; i++) {
		var element = document.getElementById(expandCollapse.arguments[i]);
		element.style.display = (element.style.display == "none") ? "block" : "none";
	}
}

</script>
