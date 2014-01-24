<div id="pop-background" style="width : 100%; height : 100%; background-color : black; position : absolute; left : 0px; top : 0px; opacity : .5"></div>
<div style="border : 1px solid black; padding : 4px; position : absolute; background-color : white; opacity : 1; left : 35%; top : 25%; min-width : 450px; max-width : 500px; height : 300px; overflow : auto">
    <div style="float : right">{LINKS}</div>
    <p><b>Report made:</b> {DATE_SENT}<br />
        <b>Reported by user:</b> {REPORTER}<br />
        <b>User reported on:</b> {OFFENDER}
    </p>
    <h2>Original Message</h2>
    <p>{MESSAGE}</p>
    <h2>Reason for report</h2>
    <p>{REASON}</p>
    <!-- BEGIN breason -->
    <h2>Reason for given for block</h2>
    <p>{BLOCK_REASON}</p>
    <!-- END breason -->
</div>
