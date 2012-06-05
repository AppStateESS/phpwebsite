<script type="text/javascript">
//<![CDATA[
var owa_baseUrl = '{OWA_URL}';
var owa_cmds = owa_cmds || [];
owa_cmds.push(['setSiteId', '{OWA_SITE_ID}']);
<!-- BEGIN OWA_CMDS -->
owa_cmds.push(['{OWA_CMD}']);
<!-- END OWA_CMDS -->

(function() {
    var _owa = document.createElement('script'); _owa.type = 'text/javascript'; _owa.async = true;
    owa_baseUrl = ('https:' == document.location.protocol ? window.owa_baseSecUrl || owa_baseUrl.replace(/http:/, 'https:') : owa_baseUrl );
    _owa.src = owa_baseUrl + 'modules/base/js/owa.tracker-combined-min.js';
    var _owa_s = document.getElementsByTagName('script')[0]; _owa_s.parentNode.insertBefore(_owa, _owa_s);
}());
//]]>
</script>
