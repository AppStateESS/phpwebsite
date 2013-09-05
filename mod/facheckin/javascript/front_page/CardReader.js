/**
 * jQuery based magnetic stripe card reader "driver" to be used in web
 * applications.
 *
 * Copyright @ 2009-2010 Jonathan Stoppani (http://garetjax.info/)
 * Licensed under the MIT license (see LICENSE for details)
 */

var CardReader = function (error_start, track_start, track_end, timeout) {
	this.error_start = error_start || "é";
	this.track_start = track_start || ";";
	this.track_end = track_end || "?";
	this.timeout = timeout || 100;

	this.error_start = this.error_start.charCodeAt(0);
	this.track_start = this.track_start.charCodeAt(0);
	this.track_end = this.track_end.charCodeAt(0);

	this.started = false;
	this.finished = false;
	this.isError = false;
	this.input = "";
	this.timer = undefined;
	this.callbacks = [];
	this.errbacks = [];
	this.validators = [];
	this.isDispatching = false;
};

CardReader.prototype = {
	dispatch: function (data, isError) {
		if (!isError) {
			for (var cb in this.validators) {
				if (!this.validators[cb](data)) {
					isError = true;
					break;
				}
			}
		}

		if (this.isDispatching) {
			if (isError) {
				console.log("Immediate error!");
				return;
			} else {
				clearTimeout(this.isDispatching);
			}
		}

		reader = this;

		this.isDispatching = setTimeout(function () {
			console.log("Error timeout cleared");
			reader.isDispatching = false;
		}, 200);

		if (isError) {
			for (var cb in this.errbacks) {
				this.errbacks[cb](this.input);
			}
		} else {
			for (var cb in this.callbacks) {
				this.callbacks[cb](this.input);
			}
		}
	},

	readObserver: function (e) {
		var ob = this;

		if (!this.started && (e.which === this.track_start || e.which === this.error_start)) {
			e.stopImmediatePropagation();
			e.preventDefault();

			this.started = true;
			this.isError = e.which === this.error_start;

			this.timer = setTimeout(function () {
				ob.started = false;
				ob.finished = false;
				ob.isError = false;
				ob.input = "";
			}, this.timeout);
		} else if (this.started && e.which === this.track_end) {
			e.stopImmediatePropagation();
			e.preventDefault();

			this.finished = true;

			clearTimeout(this.timer);
			this.timer = setTimeout(function () {
				ob.started = false;
				ob.finished = false;
				ob.isError = false;
				ob.input = "";
			}, this.timeout);
		} else if (this.started && this.finished && e.which === 13) {
			e.stopImmediatePropagation();
			e.preventDefault();

			this.dispatch(this.input, this.isError);

			this.started = false;
			this.finished = false;
			this.isError = false;
			this.input = "";

			clearTimeout(this.timer);

		} else if (this.started) {
			e.stopImmediatePropagation();
			e.preventDefault();

			this.input += String.fromCharCode(e.which);

			clearTimeout(this.timer);
			this.timer = setTimeout(function () {
				ob.started = false;
				ob.finished = false;
				ob.isError = false;
				ob.input = "";
			}, this.timeout);
		}
	},

	observe: function (element) {
		var func = this;

		$(element).keypress(function (e) {
			CardReader.prototype.readObserver.apply(func, arguments);
		});
	},

	validate: function (validator) {
		this.validators.push(validator);
	},

	cardRead: function (callback) {
		this.callbacks.push(callback);
	},

	cardError: function (errback) {
		this.errbacks.push(errback);
	},
}