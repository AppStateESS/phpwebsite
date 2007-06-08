/**
* Parses SRT lists and W3C Timed Text captions.
*
* @author	Jeroen Wijering
* @version	1.2
**/


import com.jeroenwijering.utils.StringMagic;


class com.jeroenwijering.utils.CaptionsParser {


	/** URL of the xml file to parse. **/
	private var parseURL:String;
	/** The array the XML is parsed into **/
	public var parseArray:Array;
	/** LoadVars Object the SRT file is loaded into. **/
	private var parseLV:LoadVars;
	/** Flash XML object the TT file is loaded into. **/
	private var parseXML:XML;


	/** Constructor. **/
	function CaptionsParser() {};


	/** Parse an XML list. **/
	public function parse(url:String):Void {
		parseURL = url;
		parseArray = new Array();
		parseURL.indexOf(".srt") == -1 ? parseTT(): parseSRT();
	};


	/** Convert SRT file to subtitle array **/
	private function parseSRT() {
		var ref = this;
		parseLV = new LoadVars();
		parseLV.onLoad = function(scs:Boolean) {
			if(scs) {
				var str = "";
				var j = -2;
				while(j < unescape(this).length) {
					var oj = j;
					j = unescape(this).indexOf('=&',j+2);
					j == -1 ? j = unescape(this).length: null;
					str = "&"+unescape(this).substring(oj+2,j) + str;
				}
				var arr = str.split("\r\n\r\n");
				for(var i=0; i<arr.length; i++) {
					var obj = new Object();
					var fdd = arr[i].indexOf(":");
					obj["bgn"] = Number(arr[i].substr(fdd-2,2))*3600 +
						Number(arr[i].substr(fdd+1,2))*60 + 
						Number(arr[i].substr(fdd+4,2));
					var sdd = arr[i].indexOf(":",fdd+6);
					obj["dur"] = Number(arr[i].substr(sdd-2,2))*3600 +
						Number(arr[i].substr(sdd+1,2))*60 + 
						Number(arr[i].substr(sdd+4,2)) - obj["bgn"];
					var tst = arr[i].indexOf("\r\n",sdd);
					if(arr[i].indexOf("\r\n",tst+5) > -1) {
						var brp = arr[i].indexOf("\r\n",tst+5);
						arr[i] = arr[i].substr(0,brp)+"<br />" +
							arr[i].substr(brp+2);
					}
					obj["txt"] = arr[i].substr(tst);
					ref.parseArray.push(obj);
				}
			} else { 
				parseArray.push( {txt:"File not found: " +
					ref.parseURL,bgn:1,dur:5}); 
			}
			if( parseArray.length == 0) {
				parseArray.push({txt:"Empty file: " +
					ref.parseURL,bgn:1,dur:5});
			}
			delete ref.parseLV;
			ref.onParseComplete();
		};
		if(_root._url.indexOf("file://") > -1) {
			parseLV.load(parseURL); 
		} else if(parseURL.indexOf('?') > -1) { 
			parseLV.load(parseURL+'&'+random(999)); 
		} else { 
			parseLV.load(parseURL+'?'+random(999)); 
		}
	};


	/** Covert TimedText file to subtitle array. **/
	private function parseTT():Void {
		var ref = this;
		parseXML = new XML();
		parseXML.ignoreWhite = true;
		parseXML.onLoad = function(scs:Boolean) {
			if(scs) {
				if(this.firstChild.nodeName.toLowerCase() == "tt") {
					var bdy = this.firstChild.childNodes[1];
					for(var i=0; i<bdy.childNodes.length; i++) {
						var obj = new Object();
						var bgn:String = bdy.childNodes[i].attributes.begin;
						obj["bgn"] = Number(bgn.substr(0,bgn.length-1));
						var dur:String = bdy.childNodes[i].attributes.dur;
						obj["dur"] = Number(dur.substr(0,dur.length-1));
						obj["txt"] =
							bdy.childNodes[i].firstChild.firstChild.nodeValue;
						ref.parseArray.push(obj);
					}
				}
			} else { 
				parseArray.push( {txt:"File not found: "+ref.parseURL}); 
			}
			if(parseArray.length == 0) { 
				parseArray.push({txt:"Incompatible file: "+ref.parseURL});
			}
			delete ref.parseXML;
			ref.onParseComplete();
		};
		if(_root._url.indexOf("file://") > -1) { 
			parseXML.load(parseURL); 
		} else if(parseURL.indexOf('?') > -1) {
			parseXML.load(parseURL+'&'+random(999)); 
		} else { 
			parseXML.load(parseURL+'?'+random(999)); 
		}
	};


	/** Invoked when parsing is completed. **/
	public function onParseComplete() { };


}