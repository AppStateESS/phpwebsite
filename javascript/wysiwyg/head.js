<script type="text/javascript">

function addBold(element){
        element.value = element.value + '<b>Bold Text</b>';
}

function addBreak(element){
        element.value = element.value + '<br />\\n';
}

function addItal(element){
        element.value = element.value + '<i>Italicized Text</i>';
}

function addUnder(element){
        element.value = element.value + '<u>Underlined Text</u>';
}

function addAleft(element){
        element.value = element.value + '<div align="left">Left Justified Text</div>';
}

function addAcenter(element){
        element.value = element.value + '<div align="center">Centered Text</div>';
}

function addAright(element){
        element.value = element.value + '<div align="right">Right Justified Text</div>';
}

function addUlist(element){ 
        element.value = element.value + '<ul type="disc">\r\n  <li>Item 1</li>\r\n  <li>Item 2</li>\r\n  <li>Item 3</li>\r\n</ul>\r\n';
}

function addOlist(element){ 
        element.value = element.value + '<ol type="1">\r\n  <li>Item 1</li>\r\n  <li>Item 2</li>\r\n  <li>Item 3</li>\r\n</ol>\r\n';
}

function addBlock(element){ 
        element.value = element.value + '<blockquote>\r\n  <p>Your indented text here...</p>\r\n</blockquote>\r\n';
}

function addEmail(element){ 
        element.value = element.value + '<a href="mailto:email@address.here">Click Text Here</a>';
}

function addLink(element){ 
        element.value = element.value + '<a href="http://www.web_address.here">Click Text Here</a>';
}
</script>
