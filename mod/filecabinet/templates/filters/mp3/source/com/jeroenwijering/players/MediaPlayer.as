/**
* Player that reads all media formats Flash can read.
*
* @author	Jeroen Wijering
* @version	1.9
**/


import com.jeroenwijering.players.*;


class com.jeroenwijering.players.MediaPlayer extends AbstractPlayer {


	/** Array with all config values **/
	private var config:Object = {
		clip:undefined,
		controlbar:20,
		height:undefined,
		width:undefined,
		file:"playlist.xml",
		displayheight:undefined,
		frontcolor:0x000000,
		backcolor:0xffffff,
		lightcolor:0x000000,
		autoscroll:"false",
		displaywidth:undefined,
		largecontrols:"false",
		logo:undefined,
		showdigits:"true",
		showeq:"false",
		showicons:"true",
		thumbsinplaylist:"false",
		usefullscreen:"true",
		fsbuttonlink:undefined,
		autostart:"false",
		bufferlength:3,
		overstretch:"false",
		repeat:"false",
		rotatetime:10,
		shuffle:"true",
		volume:80,
		callback:undefined,
		enablejs:"false",
		javascriptid:"",
		linkfromdisplay:"false",
		linktarget:"_self",
		streamscript:undefined,
		useaudio:"true",
		usecaptions:"true",
		usekeys:"true"
	};


	/** Constructor **/
	public function MediaPlayer(tgt:MovieClip) {
		super(tgt);
	};


	/** Setup all necessary MCV blocks. **/
	private function setupMCV() {
		// set controller
		controller = new PlayerController(config,feeder);
		// set default views
		var dpv = new DisplayView(controller,config,feeder);
		var cbv = new ControlbarView(controller,config,feeder);
		var vws:Array = new Array(dpv,cbv);
		// set optional views
		if(config["displayheight"] < config["height"]-config['controlbar'] ||
			config["displaywidth"] < config["width"]) {
			var plv = new PlaylistView(controller,config,feeder);
			vws.push(plv);
		} else {
			config["clip"].playlist._visible = false;
		}
		if(config["usekeys"] == "true") {
			var ipv = new InputView(controller,config,feeder);
			vws.push(ipv);
		}
		if(config["showeq"] == "true") {
			var eqv = new EqualizerView(controller,config,feeder);
			vws.push(eqv);
		} else {
			config["clip"].equalizer._visible = false;
		}
		if(feeder.captions == true) {
			var cpv = new CaptionsView(controller,config,feeder);
			vws.push(cpv);
		} else {
			config["clip"].captions._visible = false;
		}
		if(feeder.audio == true) {
			var adv = new AudioView(controller,config,feeder);
			vws.push(adv);
		}
		if(config["enablejs"] == "true") {
			var jsv = new JavascriptView(controller,config,feeder);
			vws.push(jsv);
		}
		if(config["callback"] != undefined) {
			var cav = new CallbackView(controller,config,feeder);
			vws.push(cav);
		}
		// set models
		var mp3 = new MP3Model(vws,controller,config,feeder,
			config["clip"]);
		var flv = new FLVModel(vws,controller,config,feeder,
			config["clip"].display.video);
		var img = new ImageModel(vws,controller,config,feeder,
			config["clip"].display.image);
		var mds:Array = new Array(mp3,flv,img);
		if(feeder.captions == true) { flv.capView = cpv; }
		// start mcv cycle
		controller.startMCV(mds);
	};


}