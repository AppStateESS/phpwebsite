<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en_US" lang="en_US">
<head>
<title>{REPORT_TITLE}</title>
<meta content="text/html; charset=UTF-8" http-equiv="Content-Type" />
<script language="Javascript1.2">
<!--
function print_page() {
window.print();
}
//-->
</script>
<style type="text/css">
body {
    font-family: sans, sans-serif, helvetica;
    font-size: 10pt;
}

h1 {
    font-size: 1.2em;
}

h2 {
    font-size: 1.1em;
}

p.peep,p.space {
    border-bottom: 1px solid black;
    width: 500px;
}

div.slot {
    margin-bottom: 2em;
}

div.print {
    text-align: right;
}

th {
    border-bottom: 1px solid black;
}
</style>
<style type="text/css" media="print">
#print {
    display: none;
}
</style>
</head>
<body>
<h1>{SHEET_TITLE}</h1>
{PRINT}
<table cellpadding="0" cellspacing="10" width="100%">
    <tr>
        <th>{NAME_LABEL}</th>
        <th>{ORGANIZATION_LABEL}</th>
        <th>{PHONE_LABEL}</th>
        <th>{EMAIL_LABEL}</th>
    </tr>
    <!-- BEGIN rows -->
    <tr>
        <td>{FIRST_NAME} {LAST_NAME}</td>
        <td>{ORGANIZATION}</td>
        <td>{PHONE}</td>
        <td>{EMAIL}</td>
    </tr>
    <!-- END rows -->
</table>
</body>
</html>
