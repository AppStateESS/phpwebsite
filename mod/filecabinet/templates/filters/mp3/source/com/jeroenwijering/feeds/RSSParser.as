/**
* Parses ATOM feeds and returns an indexed array with all elements
*
* @author	Jeroen Wijering
* @version	1.1
**/


import com.jeroenwijering.feeds.AbstractParser;
import com.jeroenwijering.utils.StringMagic;


class com.jeroenwijering.feeds.RSSParser extends AbstractParser {


	/** Contructor **/
	function RSSParser(enc:Boolean,pre:String) { super(enc,pre); };


	/** build an array with all regular elements **/
	private function setElements() {
		elements = new Object();
		elements["title"] = "title";
		elements["guid"] = "id";
		elements["author"] = "author";
		elements["category"] = "category";
		elements["link"] = "link";
		elements["geo:lat"] = "latitude";
		elements["geo:long"] = "longitude";
		elements["geo:city"] = "city";
	};


	/** Convert RSS structure to array **/
	private function parse(xml:XML):Array {
		var arr = new Array();
		var tpl = xml.firstChild.firstChild.firstChild;
		while(tpl != null) {
			if (tpl.nodeName.toLowerCase() == "item") {
				var obj = new Object();
				for(var j=0; j<tpl.childNodes.length; j++) {
					var nod:XMLNode = tpl.childNodes[j];
					var nnm = nod.nodeName.toLowerCase();
					if(elements[nnm] != undefined) {
						obj[elements[nnm]] = nod.firstChild.nodeValue;
					} else if(nnm == "description") {
						obj["description"] = StringMagic.stripTagsBreaks(
							nod.firstChild.nodeValue);
					} else if(nnm == "pubdate") {
						obj["date"] = rfc2Date(nod.firstChild.nodeValue);
					} else if(nnm == "dc:date") {
						obj["date"] = iso2Date(nod.firstChild.nodeValue);
					} else if(nnm == "media:thumbnail") {
						obj["image"] = nod.attributes.url;
					} else if(nnm == "itunes:image") {
						obj["image"] = nod.attributes.href;
					} else if(nnm == "geo") {
						obj["latitude"] = nod.attributes.latitude;
						obj["longitude"] = nod.attributes.longitude;
						obj["city"] = nod.attributes.city;
					} else if(nnm == "enclosure" || nnm == "media:content") {
						var typ = nod.attributes.type.toLowerCase();
						if(mimetypes[typ] != undefined) {
							obj["type"] = mimetypes[typ];
							obj["file"] = prefix +
								nod.attributes.url.toLowerCase();
							if(obj["file"].substr(0,4) == "rtmp") {
								obj["type"] == "rtmp";
							}
							if(nod.childNodes[0].nodeName=="media:thumbnail"){
								obj["image"]=nod.childNodes[0].attributes.url;
							}
						} else if(typ == "captions") {
							obj["captions"] = nod.attributes.url;
						} else if(typ == "audio") {
							obj["audio"] = nod.attributes.url;
						}
					} else if(nnm == "media:group") { 
						for(var k=0; k< nod.childNodes.length; k++) {
							var ncn=nod.childNodes[k].nodeName.toLowerCase();
							if(ncn == "media:content") {
								var ftp = nod.childNodes[
									k].attributes.type.toLowerCase();
								if(mimetypes[ftp] != undefined){
									obj["file"] = prefix + 
										nod.childNodes[k].attributes.url;
									obj["type"]=mimetypes[ftp];
									if(obj["file"].substr(0,4) == "rtmp") {
										obj["type"] == "rtmp";
									}
								}
							} else if(ncn == "media:thumbnail") {
								obj["image"]=nod.childNodes[k].attributes.url;
							}
						}
					}
				}
				if(obj["latitude"] == undefined && lat != undefined) {
					obj["latitude"] = lat;
					obj["longitude"] = lng;
				}
				if(obj["image"]==undefined && obj["file"].indexOf(".jpg")>0){
					obj["image"] = obj["file"];
				}
				if(obj["author"] == undefined) { obj["author"] = ttl; } 
				if(obj["type"] != undefined || enclosures == false) {
					arr.push(obj);
				}
			} else if (tpl.nodeName == "title") {
				var ttl = tpl.firstChild.nodeValue;
			} else if (tpl.nodeName == "geo:lat") { 
				var lat = tpl.firstChild.nodeValue; 
			} else if (tpl.nodeName == "geo:long") { 
				var lng = tpl.firstChild.nodeValue;
			}
			tpl = tpl.nextSibling;
		}
		return arr;
	};


}