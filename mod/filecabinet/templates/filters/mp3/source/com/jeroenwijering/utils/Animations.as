/**
* A couple of commonly used animation functions.
*
* @author	Jeroen Wijering
* @version	1.2
**/


class com.jeroenwijering.utils.Animations {


	/**
	* Fadein function for MovieClip.
	*
	* @param tgt	Movieclip to fade.
	* @param end	Final alpha value.
	* @param inc	Speed of the fade (increment per frame).
	**/
	public static function fadeIn(tgt:MovieClip,end:Number,spd:Number):Void {
		arguments.length < 3 ? spd = 20: null;
		arguments.length < 2 ? end = 100: null;
		tgt._visible = true;
		tgt.onEnterFrame = function() {
			if(this._alpha > end-spd) {
				delete this.onEnterFrame;
				this._alpha = end;
			} else {
				this._alpha += spd;
			}
		};
	};


	/**
	* Fadeout function for MovieClip.
	*
	* @param tgt	Movieclip to fade.
	* @param end	Final alpha value.
	* @param inc	Speed of the fade (increment per frame).
	* @param rmv	Remove the clip after fadeout.
	**/
	public static function fadeOut(tgt:MovieClip,end:Number,spd:Number,rmv:Boolean):Void {
		arguments.length < 4 ? rmv = false: null;
		arguments.length < 3 ? spd = 20: null;
		arguments.length < 2 ? end = 0: null;
		tgt.onEnterFrame = function() {
			if(this._alpha < end+spd) {
				delete this.onEnterFrame;
				this._alpha = end;
				end == 0 ? this._visible = false: null;
				rmv == true ? this.removeMovieClip(): null;
			} else {
				this._alpha -= spd;
			}
		};
	};


	/** 
	* Crossfade a given MovieClip to/from to 0.
	* 
	* @param tgt	Movieclip to fade.
	* @param alp	Top alpha value. 
	**/
	public static function crossfade(tgt:MovieClip, alp:Number) {
		var phs = "out";
		var pct = alp/5;
		tgt.onEnterFrame = function() {
			if(phs == "out") {
				this._alpha -= pct;
				if (this._alpha < 1) { phs = "in"; }
			} else {
				this._alpha += pct;
				this._alpha >= alp ? delete this.onEnterFrame : null; 
			}
		}; 
	};


	/**
	* Easing enterframe function for a Movieclip.
	*
	* @param tgt	MovieClip of the balloon to iterate
	* @param xps	Final x position.
	* @param yps	Final y position.
	* @param spd	Speed of the ease (1 to 10)
	**/
	public static function easeTo(tgt:MovieClip,xps:Number,yps:Number,spd:Number):Void {
		arguments.length < 4 ? spd = 2: null;
		tgt.onEnterFrame = function() {
			this._x = xps-(xps-this._x)/(1+1/spd);
			this._y = yps-(yps-this._y)/(1+1/spd);
			if (this._x>xps-1 && this._x<xps+1 && this._y>yps-1 && this._y<yps+1) {
				this._x = Math.round(xps);
				this._y = Math.round(yps);
				delete this.onEnterFrame;
			} 
		}; 
	};


	/** 
	* Ease typewrite text into a tag after a given delay. 
	*
	* @param tgt	Movieclip to draw the shape into.
	* @param rnd	Random number of frames to wait.
	* @param txt	(optionally) text to write (else tf's current text is used)
	**/
	public static function easeText(tgt:MovieClip,rnd:Number,txt:String) {
		if (arguments.length == 2) {
			tgt.str = tgt.tf.text;
			tgt.hstr = tgt.tf.htmlText;
		} else { tgt.str = tgt.hstr = txt; }
		tgt.tf.text = "";
		tgt.i = 0;
		tgt.rnd = rnd;
		tgt.onEnterFrame = function() {
			if(this.i > this.rnd) { 
				this.tf.text = this.str.substr(0, this.str.length - Math.floor((this.str.length - this.tf.text.length)/1.4));
			}
			if(this.tf.text == this.str) {
				this.tf.htmlText = this.hstr;
				if(this.more != undefined) { this.more._visible = true; }
				delete this.onEnterFrame;
			}
			this.i++;
		};
	};


	/**
	* Make a Movieclip jump to a specific scale
	*
	* @param tgt	Movieclip that should jump.
	* @param scl	Final scale.
	* @param spd	Scaling speed.
	**/
	public static function jump(tgt:MovieClip,scl:Number,spd:Number):Void {
		arguments.length < 2 ? scl = 100: null;
		arguments.length < 3 ? spd = 1: null;
		tgt.onEnterFrame = function() {
			this._xscale = this._yscale = scl-(scl-this._xscale)/(1+1/scl);
			if(this._xscale > scl - 1 && this._xscale < scl + 1) {
				delete this.onEnterFrame;
				this._xscale = this._yscale = scl;
			} 
		};
	};


	/**
	* Transform the color of a MovieClip over time
	*
	* @param tgt	Target MovieClip.
	* @param red	Red channel offset.
	* @param gre	Green channel offset.
	* @param blu	Blue channel offset.
	* @param dur	Duration of the transformation (1 to 100).
	**/
	public static function setColor(tgt:MovieClip,red:Number,gre:Number,blu:Number,dur:Number):Void {
		arguments.length < 5 ? dur = 5: null;
		tgt.col = new Color(tgt);
		tgt.cr = tgt.cg = tgt.cb = 0;
		tgt.onEnterFrame = function() {
			this.cr = this.cr+(red-this.cr)/dur;
			this.cg = this.cg+(gre-this.cg)/dur;
			this.cb = this.cb+(blu-this.cb)/dur;
			this.col.setTransform({rb:this.cr, gb:this.cg, bb:this.cb});
			if (Math.abs(this.cr-red)<2 && Math.abs(this.cg-gre)<2 && Math.abs(this.cb-blu)<2) {
				delete this.onEnterFrame;
				this.col.setTransform({rb:red, gb:gre, bb:blu}); 
			}  
		}; 
	};

}