<script type="text/javascript">
//<![CDATA[

<!-- Original:  Phil Webb (phil@philwebb.com) -->
<!-- Web Site:  http://www.philwebb.com -->

<!-- This script and many more are available free online at -->
<!-- The JavaScript Source!! http://javascript.internet.com -->

function move(fbox, tbox, sort) {
  var arrFbox = new Array();
  var arrTbox = new Array();
  var arrLookup = new Array();
  var i;
  for (i = 0; i < tbox.options.length; i++) {
    arrLookup[tbox.options[i].text] = tbox.options[i].value;
    arrTbox[i] = tbox.options[i].text;
  }
  var fLength = 0;
  var tLength = arrTbox.length;
  for(i = 0; i < fbox.options.length; i++) {
    arrLookup[fbox.options[i].text] = fbox.options[i].value;
    if (fbox.options[i].selected && fbox.options[i].value != "") {
      arrTbox[tLength] = fbox.options[i].text;
      tLength++;
    }
    else {
      arrFbox[fLength] = fbox.options[i].text;
      fLength++;
    }
  }

  if(sort == true) {
   arrFbox.sort(compare);
   arrTbox.sort(compare);
  }

  fbox.length = 0;
  tbox.length = 0;
  var c;
  for(c = 0; c < arrFbox.length; c++) {
    var no = new Option();
    no.value = arrLookup[arrFbox[c]];
    no.text = arrFbox[c];
    fbox[c] = no;
  }
  for(c = 0; c < arrTbox.length; c++) {
    var no = new Option();
    no.value = arrLookup[arrTbox[c]];
    no.text = arrTbox[c];
    tbox[c] = no;
  }
}

function compare(a, b) {
  var anew = a.toLowerCase();
  var bnew = b.toLowerCase();
  if (anew < bnew) return -1;
  if (anew > bnew) return 1;
  return 0;
}

function selectAll(box) {
    for(var i=0; i<box.length; i++) {
        box.options[i].selected = true;
    }
}

//]]>
</script>
