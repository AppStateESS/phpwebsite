/**
* Extra audiotrack management of the players MCV pattern.
*
* @author	Jeroen Wijering
* @version	1.0
**/


import com.jeroenwijering.players.*;

class com.jeroenwijering.players.AudioView extends AbstractView { 


	/** The MovieClip to which the sounds will be attached **/
	private var audioClip:MovieClip;
	/** The Sound object we'll use**/
	private var audioObject:Sound;
	/** The current elapsed time **/
	private var currentTime:Number = 0;
	/** The current audio time **/
	private var audioTime:Number;
	/** Save the current state **/
	private var currentState:Number;


	/** Constructor, loads caption file. **/
	function AudioView(ctr:AbstractController,cfg:Object,fed:Object) {
		super(ctr,cfg,fed);
		var ref = this;
		audioClip = config['clip'].createEmptyMovieClip('audio',
			config['clip'].getNextHighestDepth());
		audioClip.setStart = function() { 
			if(ref.currentState == 2) {
				ref.audioObject.start(currentTime);
			}
		};
		audioClip.setStop = function() { ref.audioObject.stop(); };
		audioObject = new Sound (audioClip);
		audioObject.setVolume(80);
	};


	private function setItem(idx:Number) {
		audioObject.loadSound(feeder.feed[idx]['audio'],true);
	};


	private function setState(stt:Number) {
		currentState = stt;
		if(stt == 2 && config['useaudio'] == "true") {
			audioObject.start(currentTime,99);
		} else {
			audioObject.stop();
		}
	};



	private function setTime(elp:Number,rem:Number) {
		if(Math.abs(elp-currentTime) > 1) {
			currentTime = elp;
			audioTime = audioObject.position/1000;
			if(Math.abs(currentTime - audioTime) > 1 &&
				config['useaudio'] == "true") {
				audioObject.start(currentTime);
			}
		}
	};

}