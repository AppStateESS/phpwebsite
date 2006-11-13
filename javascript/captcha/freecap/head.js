<script language="javascript">
//<![CDATA[

function new_freecap()
{
	// loads new freeCap image
	if(document.getElementById)
	{
		// extract image name from image source (i.e. cut off ?randomness)
		thesrc = document.getElementById("freecap").src;
		thesrc = thesrc.substring(0,thesrc.lastIndexOf(".")+4);
		// add ?(random) to prevent browser/isp caching
		document.getElementById("freecap").src = thesrc+"?"+Math.round(Math.random()*100000);
	} else {
		alert("Sorry, cannot autoreload freeCap image\nSubmit the form and a new freeCap will be loaded");
	}
}
//]>
</script>
