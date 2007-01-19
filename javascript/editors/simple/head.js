<script type="text/javascript">
//<![CDATA[

/**
 * @version $Id$
 * @author Matthew McNaney
 * @author Steven Levin
 * @author Shaun Murray
 */

function insert(id, tag, desc) {
     var input = document.getElementById(id);

     //	var input = document.forms[form].elements[section];
	if((tag=='left')||(tag=='right')||(tag=='center')) {
		var aTag = '<div align="' + tag + '">';
		var eTag = '</div>';
	} else if(tag=='br')  {
		var aTag = '';
		var eTag = '<' + tag + ' />';
	} else if(tag=='block')  {
		var aTag = '<blockquote><p>';
		var eTag = '</p></blockquote>';
	} else if (tag=='link') {
		var url = prompt('Please enter the url', 'http://');
		if (!(url) || (url=='http://'))	{url = 'http://www.yourlink.here'}
		var aTag = '<a href="' + url + '">';
		var eTag = '</a>';
	} else if (tag=='email') {
		var url = prompt('Please enter the email address', 'mailto:');
		if (!(url) || (url=='mailto:'))	{url = 'mailto:your.email@domain.com'}
		var aTag = '<a href="' + url + '">';
		var eTag = '</a>';
	} else if (tag=='olist') {
		var aTag = '\r\n<ol type="1">\r\n  <li>Item 1</li>\r\n  <li>Item 2</li>\r\n  <li>Item 3</li>\r\n</ol>\r\n';
		var eTag = '';
	} else if (tag=='ulist') {
		var aTag = '\r\n<ul type="disc">\r\n  <li>Item 1</li>\r\n  <li>Item 2</li>\r\n  <li>Item 3</li>\r\n</ul>\r\n';
		var eTag = '';
	} else {
		var aTag = '<' + tag + '>';  // our open tag
		var eTag = '</' + tag + '>'; // our close tag
	}
	input.focus();
	if(typeof document.selection != 'undefined') {	// For Internet Explorer
		if(document.getSelection) { //Try this for MacIE
			var insText = prompt('Please enter the text you\'d like to' + desc + ':');
		} else { //Or Win IE
			var range = document.selection.createRange();
			var insText = range.text;
		}
		if ((insText.length == 0) && ((tag == 'link') || (tag == 'email')))	{
			insText = prompt('Please enter a description', '');
		}
		if(document.getSelection) { //Try this for MacIE
		insText = aTag + insText + eTag;
        form = document.getElementsByName(form)[0];
        eval('form.'+section+'.value=form.'+section+'.value + insText');
		} else { //Or Win IE
			range.text = aTag + insText + eTag;
			range = document.selection.createRange();
			if (insText.length == 0) {
				if((tag == 'olist') || (tag == 'ulist')) { range.move('character', aTag.length + eTag.length -6); }
				else { range.move('character', -eTag.length); }
			} else {
				if((tag == 'olist') || (tag == 'ulist')) { range.move('character', aTag.length + eTag.length -6); }
				else { range.moveStart('character', aTag.length + insText.length + eTag.length); }
			}
			range.select();
		}
	} else if(typeof input.selectionStart != 'undefined') { // For newer Gecko based Browsers
		var start = input.selectionStart;
		var end = input.selectionEnd;
		var insText = input.value.substring(start, end);
		if ((insText.length == 0) && ((tag == 'link') || (tag == 'email')))	{
			insText = prompt('Please enter a description', '');
		}
		input.value = input.value.substr(0, start) + aTag + insText + eTag + input.value.substr(end);
		var pos;
		if (insText.length == 0) {
			if((tag == 'olist') || (tag == 'ulist')) { pos = start + aTag.length + eTag.length - 6; }
			else { pos = start + aTag.length; }
		} else {
			if((tag == 'olist') || (tag == 'ulist')) { pos = start + aTag.length + eTag.length - 6; }
			else { pos = start + aTag.length + insText.length + eTag.length; }
		}
		input.selectionStart = pos;
		input.selectionEnd = pos;
	} else {	// All the rest
		var pos = input.value.length;
		var insText = prompt('Please enter the text you\'d like to'+ desc +':');
		input.value = input.value.substr(0, pos) + aTag + insText + eTag + input.value.substr(pos);
	}
}
//]]>
</script>
