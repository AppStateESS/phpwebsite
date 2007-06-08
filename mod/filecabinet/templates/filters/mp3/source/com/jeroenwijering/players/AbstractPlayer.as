/**
* Abstract player class, extended by all other players.
* Class loads config and file objects and sets up MCV triangle.
*
* @author	Jeroen Wijering
* @version	1.9
**/


import com.jeroenwijering.players.*;
import com.jeroenwijering.feeds.*;


class com.jeroenwijering.players.AbstractPlayer implements FeedListener {


	/** Object with all config values **/
	private var config:Object;
	/** Object with all playlist items **/
	public var feeder:FeedManager;
	/** reference to the controller **/
	public var controller:AbstractController;


	/** Player application startup. **/
	public function AbstractPlayer(tgt:MovieClip) {
		config["clip"] = tgt;
		config["clip"]._visible = false;
		loadConfig();
	};


	/** Set config variables or load them from flashvars. **/
	private function loadConfig() {
		config["width"] = Stage.width;
		config["height"] = Stage.height;
		for(var cfv in config) {
			if(_root[cfv] != undefined) {
				config[cfv] = unescape(_root[cfv]);
			}
		}
		if(config['largecontrols'] == "true") { config["controlbar"] *= 2; }
		if (config["displayheight"] == undefined) {
			config["displayheight"] = config["height"] - config['controlbar'];
		} else if(Number(config["displayheight"])>Number(config["height"])) {
			config["displayheight"] = config["height"];
		}
		if (config["displaywidth"] == undefined) {
			config["displaywidth"] = config["width"];
		}
		feeder = new FeedManager(true,config["enablejs"],_root.prefix);
		feeder.addListener(this);
		feeder.loadFile({file:config["file"]});
	};


	/** Invoked by the feedmanager **/
	public function onFeedUpdate() {
		if(controller == undefined) {
			config["clip"]._visible = true;
			_root.activity._visible = false;
			setupMCV();
		}
	};


	/** Setup all necessary MCV blocks. **/
	private function setupMCV() {
		controller = new AbstractController(config,feeder);
		var asv = new AbstractView(controller,config,feeder);
		var vws:Array = new Array(asv);
		var asm = new AbstractModel(vws,controller,config,feeder);
		var mds:Array = new Array(asm);
		controller.startMCV(mds);
	};


}