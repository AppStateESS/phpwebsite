<?xml version="1.0" encoding="utf-8"?>
<rdf:RDF xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#" xmlns="http://purl.org/rss/1.0/">
    <channel rdf:about="{CHANNEL_ADDRESS}">
    <title>{CHANNEL_TITLE}</title>
    <description> {CHANNEL_DESCRIPTION} </description>
    <link>{HOME_ADDRESS}</link>
    <!-- BEGIN image-resource --><image rdf:resource="{IMAGE_LINK}" /><!-- END image-resource -->
    <textinput rdf:resource="{SEARCH_LINK}" /> <items> <rdf:Seq>
        <!-- BEGIN item-about -->
        <li rdf:resource="{ITEM_LINK}" />
        <!-- END item-about -->
    </rdf:Seq> </items> </channel>
    <!-- BEGIN channel-image -->
    <image rdf:about="{IMAGE_LINK}">
    <title>{IMAGE_TITLE}</title>
    <url>{IMAGE_URL}</url>
    <link>{IMAGE_LINK}</link>
    </image>
    <!-- END channel-image -->
    <!-- BEGIN item-listing -->
    <item rdf:about="{ITEM_LINK}">
    <title>{ITEM_TITLE}</title>
    <description>{ITEM_DESCRIPTION}</description>
    <link>{ITEM_LINK}</link>
    <dc:creator>{ITEM_AUTHOR}</dc:creator> <dc:date>{ITEM_DC_DATE}</dc:date>
    <dc:type>{ITEM_DC_TYPE}</dc:type> </item>
    <!-- END item-listing -->
    <textinput rdf:about="{SEARCH_LINK}">
    <title>{CHANNEL_TITLE}</title>
    <description>{SEARCH_DESCRIPTION}</description> <name>{SEARCH_NAME}</name>
    <link>{SEARCH_LINK}</link>
    </textinput>
</rdf:RDF>
