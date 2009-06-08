<script type="text/javascript">
//<![CDATA[

    function checkQty(min, max, type, target) {

        var text_min = '{text_min}';
        var text_min_2 = '{text_min_2}';
        var text_max = '{text_max}';
        var text_max_2 = '{text_max_2}';
        var text_ok = '{text_ok}';
        var text_ok_2 = '{text_ok_2}';
        var text_ok_3 = '{text_ok_3}';
        var question = null;
        
        if (type) {
            type = type;
        } else {
            var type = '{type}';
        }
        
        if (type == 'check') {
            var qty = getTotalChecks();
        } else {
            var qty = getTotalVotes();
        }

        if (qty < min) {
            question = text_min + min.toString() + text_min_2;
            alert(question);
        } else if (qty > max) {
            var extra = max - qty;
            question = text_max + extra.toString() + text_max_2;
            alert(question);
        } else {
            question = text_ok + qty.toString() + text_ok_2 + max.toString() + text_ok_3;
            if (confirm(question)) {
                target.submit();
            }
        }
        

    }

    function getTotalVotes() {
        oTextBoxes = new Array(); // to store the textbox objects
        oInputs = document.getElementsByTagName( 'input' ) // store collection of all <input/> elements
        for ( i = 0; i < oInputs.length; i++ ) { // loop through and find <input type="text"/>
            if ( oInputs[i].type == 'text' ) {
                oTextBoxes.push( oInputs[i] ); // found one - store it in the oTextBoxes array
            }
        }
        msg = "Found " + oTextBoxes.length + " text boxes";
        var count = 0;
        var total = 0;
        for ( i = 0; i < oTextBoxes.length; i++ ) { // Loop through the stored textboxes and output the value
            msg += "\nTextbox #" + ( i + 1 ) + " value = " + oTextBoxes[i].value;
            if (oTextBoxes[i].value !="") {
                if (isNaN(oTextBoxes[i].value)) {
                    var votes = 0;
                } else {
                    count ++;
                    var votes = Number(oTextBoxes[i].value);
                }
            } else {
                var votes = 0;
            }
            total = total + (votes * 1);
        }
//        alert( msg );
//        alert (count + " textboxes were filled in " + total + " votes");
        return total;
    }

    function getTotalChecks() {
        oCheckBoxes = new Array(); // to store the checkbox objects
        oInputs = document.getElementsByTagName( 'input' ) // store collection of all <input/> elements
        for ( i = 0; i < oInputs.length; i++ ) { // loop through and find <input type="checkbox"/>
            if ( oInputs[i].type == 'checkbox' ) {
                oCheckBoxes.push( oInputs[i] ); // found one - store it in the oCheckBoxes array
            }
        }
        msg = "Found " + oCheckBoxes.length + " check boxes";
        var count = 0;
        for ( i = 0; i < oCheckBoxes.length; i++ ) { // Loop through the stored checkboxes and see if it's checked
            msg += "\nCheckbox #" + ( i + 1 ) + " checked = " + oCheckBoxes[i].checked;
            if (oCheckBoxes[i].checked) {
                count ++;
            }
        }
//        alert( msg );
//        alert (count + " textboxes were checked");
        return count;
    }


//]]>
</script>


