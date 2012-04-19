<div class="naccleblock">
{TYPE_TITLE}: 
<select id="nacfcm-{ID}" name="nacfcm-{NAME}" class="nacfcselect nacfcselectm" data-pre="{ID}" onchange="nacFcM(this)">
<option value="I">Images</option>
<option value="D">Documents</option>
<option value="M">MultiMedia</option>
</select>
{FOLDER_TITLE}: 
<select id="nacfcf-{ID}" name="nacfcf-{NAME}" class="nacfcselect nacfcselectf" data-pre="{ID}" onchange="nacFcFc(this)" onClick="nacFcF(this)">
<option value=" ">...</option>
</select>
{FILES_TITLE}: 
<select id="nacfcp-{ID}" name="nacfcp-{NAME}" class="nacfcselect nacfcselectp" data-pre="{ID}" onchange="nacFcP(this)">
<option value=" ">...</option>
</select>
&nbsp;<a data-pre="{ID}" onClick="nacFcTnClear(this)">clear fields</a>
<textarea id="{ID}" name="{NAME}" class="nacarea"></textarea>
<div id="nacfctnfcf-{ID}" class="nacfcthumbs"> </div>
<div id="nacwrap-{ID}" class="nodisplay">{VALUE}</div>
</div>
<script type="text/javascript">
//<![CDATA[

	// author Hilmar Runge
	// 2012.04
	
	// bg module identifier
	var mid	= 'nac';
	
	var authkey = '{authkey}';
	var source_http = '{source_http}';
	
	var nacfcopt0 = '<option value=" ">...<\/option>';
	var nacdebug4 = '';
	
	var nacclfc = new Array();
		// ["artifical_instance"] =
		// {
		//	"m" : 'media type class',
		//	"f" : 'folder name',
		//	"p" : 'pic or file name',
		//	"t" : 'title'
		//	"y" : 'id of db table row'
		// }

	$(document).ready(function(){
		// instance "#{ID}" ...summary ...entry = twice for blog i.e.
		if ($("#{ID}").is("#{ID}")) {
			// feed the textarea with the value
			$("#{ID}").val($("#nacwrap-{ID}").html());
			$("#nacwrap-{ID}").remove();
			clSetTbbFc();
			// note clfcx: is an artifical (non cle std) property representing the instance
			$("#{ID}").cleditor({
				clfcx:	"{ID}",
				width:	"99%",
				height:	"99%",
				controls: $.cleditor.defaultOptions.controls.replace("image", "image pwsfc")
			});
		}
	});


	function nacFcM(my) {
		// onchange fcm the fc type
		var x=$(my).attr('data-pre');
		// nacclfc[x].m ... I image folders, D document folders, M media folders 
		nacFcTnClear(my);
		nacclfc[x]={
					m: $('#'+my.id).val()
				};
	}
	
	function nacFcF(my) {
		// onclick fcf
		// console.log('fcf-click');
		var x=$(my).attr('data-pre');
		if (!(x in nacclfc)) {
			// initial to images if types not touched before
			nacclfc[x]={
						m: "I"
					};
		}
		if (!('f' in nacclfc[x])) {
			// prevent re-retrieval the same
			if ($('#'+my.id+' option').length > 1) return;
			
			switch (nacclfc[x].m) {
				case "I" :
					nacUniCS('','fcfi',my.id);
					break;
				case "D" :
					nacUniCS('','fcfd',my.id);
					break;
				case "M" :
					nacUniCS('','fcfm',my.id);
					break;
			}
		}
	}
	
	function nacFcFc(my) {
		// onchange fcf
		// console.log('fcf-change');
		var x=$(my).attr('data-pre');
		var fcff=$('#'+my.id).val(); // selected folder title
		nacclfc[x]={
					m: $('#nacfcm-'+x).val(),
					f: fcff 
				};
		if (fcff==' ') {
			$('#nacfctnfcf-'+x).html(' ');
			$('#nacfcp-'+x).html(nacfcopt0);
		} else {
			nacUniCS('/s/'+fcff,('fcp'+nacclfc[x].m.toLowerCase()),my.id);
		}
	}
	
	function nacFcP(my) {
		// onchange fcp or triggered by nacFcTn
		var x=$(my).attr('data-pre');
		// selected file
		nacclfc[x].p = $('#'+my.id).val(); 
		nacclfc[x].t = $('#'+my.id+' option:selected').text();
		// the selected option data-pre value
		nacclfc[x].y = $($('#'+my.id+' option:selected').prop('attributes')['data-pre']).val();
	}
	
	function nacFcTn(my) {
		//onclick a tn
		var s = $(my).attr('src').replace('/tn/','/');
		var t = '#' + $(my).parent().attr('id').replace('nacfctnfcf-','nacfcp-');
		$(t).val(s);
		$(t).trigger('change');
	}
		
	function nacFcTnClear(my) {
		var x=$(my).attr('data-pre');
		$('#nacfctnfcf-'+x).html(' ');
		$('#nacfcf-'+x).html(nacfcopt0);
		$('#nacfcf-'+x).val(' ');
		$('#nacfcp-'+x).html(nacfcopt0);
		$('#nacfcp-'+x).val(' ');
		delete nacclfc[x];
	}

	// CL if
	
	function clSetTbbFc() {
		$.cleditor.buttons.pwsfc = {
			name: "pwsfc",
			image: "fc20.png",
			title: "Insert from pws FileCabinet",
			command: "inserthtml",
			buttonClick: clClickTbbFc
		};
	}
	
	function clClickTbbFc(e, data) {
		// the cl fc icon click
		var editor = data.editor;
		var inst = editor.options.clfcx;
		var cnt = '';
		// design mode to return true = nothing to do, false = done
		if (!(nacclfc[inst])) return false;
		switch (nacclfc[inst].m) {
			case 'I':
				cnt = '<img src="' + source_http + nacclfc[inst].p + '" alt=" *noPic* "\/>';
				break;
			case 'D': 
				cnt = '<a href="' + source_http + nacclfc[inst].p + '">' + nacclfc[inst].t + '<\/a>';
				break;
			case 'M':
				cnt = '[filecabinet:media:' + nacclfc[inst].y + ']';
				break;
		}
		editor.execCommand(data.command, cnt, null, data.button);
		editor.focus;
		return false;
	}

	function nacUniCS(ref,op,i) {
		var url = 'ngcom/xaop/';
		switch (op) {
			case 'fcfi': case 'fcfd': case 'fcfm':
			case 'fcpi': case 'fcpd': case 'fcpm':
				url = url + op;
				break;
			default:
				return;
		}
		url = url + ref + '/authkey/' + authkey; 
		$.ajax({
			type: "GET",
			url: url,
			success: function(reply) 
			{
				var jso = jQuery.parseJSON(reply);
				if (!jso) return;
				if (jso.mid==mid) {
					switch (op) {
					case 'fcfi': case 'fcfm':
						$('#'+i).html(jso.fonas);
						break;
					case 'fcfd': 
						$('#'+i).html(jso.fonas);
						// in chrome the select field pre values of fcfd remain asis on screen (unrefreshed)
						// after a 2nd click the new content (options) are seen correct
						// ff=ok, opera=ok, webkit=no solution at the moment 201204
						break;
					case 'fcpi': case 'fcpd': case 'fcpm':
						var tn = '#nacfctnfcf-'+i.replace('nacfcf-','');
						var wo = '#nacfcp-'+i.replace('nacfcf-','');
						$(tn).html(jso.picas);
						$(wo).html(jso.pinas);
						break;
					}
				}
			}
		});
	}
	
//]]>
</script>