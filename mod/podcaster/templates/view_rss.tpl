<?xml version="1.0" encoding="utf-8"?>
<rss xmlns:itunes="http://www.itunes.com/dtds/podcast-1.0.dtd" xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:atom="http://www.w3.org/2005/Atom" version="2.0">

<channel>

    <title>{CHANNEL_TITLE}</title>
    <link>{CHANNEL_ADDRESS}</link>
    <atom:link href="{CHANNEL_ADDRESS}" rel="self" type="application/rss+xml" />
    <description>{CHANNEL_DESCRIPTION}</description>
    <language>{LANGUAGE}</language>
    <lastBuildDate>{LAST_BUILD_DATE}</lastBuildDate>
    <docs>{CHANNEL_ADDRESS}</docs>
    <generator>phpWebSite Podcaster module</generator>
    <managingEditor>{MANAGING_EDITOR}</managingEditor>
    <webMaster>{WEBMASTER}</webMaster>
    <copyright>{COPYRIGHT}</copyright>

    <!-- BEGIN category --><category>{CATEGORY}</category><!-- END category -->

    <itunes:author>{CHANNEL_OWNER} @ {SITE_TITLE}</itunes:author>
    <itunes:subtitle>{CHANNEL_DESCRIPTION_PREAMBLE}</itunes:subtitle>
    <itunes:summary>{CHANNEL_DESCRIPTION}</itunes:summary>
    <itunes:owner>
        <itunes:name>{CHANNEL_OWNER}</itunes:name>
        <itunes:email>{MANAGING_EDITOR}</itunes:email>
    </itunes:owner>
    <image>
        <url>{THUMB_URL}</url>
        <title>{CHANNEL_TITLE}</title>
        <link>{HOME_ADDRESS}</link>
    </image>
    {ITUNES_CATEGORY}
    <itunes:explicit>{ITUNES_EXPLICIT}</itunes:explicit>

    <!-- BEGIN item-listing -->
    <item>
        <title>{ITEM_TITLE}</title>
        <link>{ITEM_LINK}</link>
        <description>{ITEM_DESCRIPTION}</description>
        <enclosure url="{ITEM_URL}" length="{ITEM_LENGTH}" type="{ITEM_TYPE}" />
        <dc:creator>{ITEM_AUTHOR}</dc:creator>
        <pubDate>{ITEM_PUBDATE}</pubDate>
        <guid isPermaLink="true">{ITEM_GUID}</guid>
        <itunes:author>{ITEM_AUTHOR} @ {SITE_TITLE}</itunes:author>
        <itunes:subtitle>{ITEM_DESCRIPTION_PREAMBLE}</itunes:subtitle>
        <itunes:summary>{ITEM_DESCRIPTION}</itunes:summary>
    </item>
    
    <!-- END item-listing -->

</channel>
</rss>
