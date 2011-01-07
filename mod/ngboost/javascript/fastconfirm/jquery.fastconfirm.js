/*
 * jQuery Fast Confirm
 * version: 1.1.0 (2010-09-28)
 * @requires jQuery v1.3.2 or later
 *
 * Examples and documentation at: http://blog.pierrejeanparra.com/jquery-plugins/fast-confirm/
 * Dual licensed under the MIT and GPL licenses:
 *   http://www.opensource.org/licenses/mit-license.php
 *   http://www.gnu.org/licenses/gpl.html
 */
;(function($) {
	
	$.fn.fastConfirm = function(options) {
		var params;
		
		if (!this.length) {
			return this;
		}
			
		$.fastConfirm = {
			defaults: {
				position: 'bottom',
				offset: {top: 0, left: 0},
				questionText: "Are you sure?",
				proceedText: "Yes",
				cancelText: "No",
				fastConfirmClass: "fast_confirm",
				onProceed: function(trigger) {},
				onCancel: function(trigger) {}
			}
		};
		
		params = $.extend($.fastConfirm.defaults, options || {});
		
		return this.each(function() {
			var offset,
				topOffset,
				leftOffset,
				trigger = this,
				$confirmYes = $('<button class="' + params.fastConfirmClass + '_proceed">' + params.proceedText + '</button>'),
				$confirmNo = $('<button class="' + params.fastConfirmClass + '_cancel">' + params.cancelText + '</button>'),
				$confirmBox = $('<div class="' + params.fastConfirmClass + '"><div class="' + params.fastConfirmClass + '_arrow_border"></div><div class="' + params.fastConfirmClass + '_arrow"></div>' + params.questionText + '<br/></div>'),
				$arrow = $('div.' + params.fastConfirmClass + '_arrow', $confirmBox),
				$arrowBorder = $('div.' + params.fastConfirmClass + '_arrow_border', $confirmBox),
				confirmBoxArrowClass,
				confirmBoxArrowBorderClass,
				offset = $(trigger).offset();
			
			if (!$(trigger).data('fastconfirm_binded')) {
				// Register actions
				$confirmYes.click(function() {
					params.onProceed(trigger);
					$(trigger).removeData('fastconfirm_binded');
					$(this).closest('div.' + params.fastConfirmClass).remove();
				});
				
				$confirmNo.click(function() {
					params.onCancel(trigger);
					$(trigger).removeData('fastconfirm_binded');
					$(this).closest('div.' + params.fastConfirmClass).remove();
				});
				
				// Append the confirm box to the body. It will not be visible as it is off-screen by default. Positionning will be done at the last time
				$confirmBox.append($confirmYes).append($confirmNo);
				$('body').append($confirmBox);
				
				// Calculate absolute positionning depending on the trigger-relative position 
				switch (params.position) {
					case 'top':
						confirmBoxArrowClass = params.fastConfirmClass + '_bottom';
						confirmBoxArrowBorderClass = params.fastConfirmClass + '_bottom';
						
						$arrow.addClass(confirmBoxArrowClass).css('left', $confirmBox.outerWidth()/2 - $arrow.outerWidth()/2);
						$arrowBorder.addClass(confirmBoxArrowBorderClass).css('left', $confirmBox.outerWidth()/2 - $arrowBorder.outerWidth()/2);
						
						topOffset = offset.top - $confirmBox.outerHeight() - $arrowBorder.outerHeight() + params.offset.top;
						leftOffset = offset.left - $confirmBox.outerWidth()/2 + $(trigger).outerWidth()/2 + params.offset.left;
						break;
					case 'right':
						confirmBoxArrowClass = params.fastConfirmClass + '_left';
						confirmBoxArrowBorderClass = params.fastConfirmClass + '_left';
						
						$arrow.addClass(confirmBoxArrowClass).css('top', $confirmBox.outerHeight()/2 - $arrow.outerHeight()/2);
						$arrowBorder.addClass(confirmBoxArrowBorderClass).css('top', $confirmBox.outerHeight()/2 - $arrowBorder.outerHeight()/2);
						
						topOffset = offset.top + $(trigger).outerHeight()/2 - $confirmBox.outerHeight()/2 + params.offset.top;
						leftOffset = offset.left + $(trigger).outerWidth() + $arrowBorder.outerWidth() + params.offset.left;
						break;
					case 'bottom':
						confirmBoxArrowClass = params.fastConfirmClass + '_top';
						confirmBoxArrowBorderClass = params.fastConfirmClass + '_top';
						
						$arrow.addClass(confirmBoxArrowClass).css('left', $confirmBox.outerWidth()/2 - $arrow.outerWidth()/2);
						$arrowBorder.addClass(confirmBoxArrowBorderClass).css('left', $confirmBox.outerWidth()/2 - $arrowBorder.outerWidth()/2);
						
						topOffset = offset.top + $(trigger).outerHeight() + $arrowBorder.outerHeight() + params.offset.top;
						leftOffset = offset.left - $confirmBox.outerWidth()/2 + $(trigger).outerWidth()/2 + params.offset.left;
						break;
					case 'left':
						confirmBoxArrowClass = params.fastConfirmClass + '_right';
						confirmBoxArrowBorderClass = params.fastConfirmClass + '_right';
						
						$arrow.addClass(confirmBoxArrowClass).css('top', $confirmBox.outerHeight()/2 - $arrow.outerHeight()/2);
						$arrowBorder.addClass(confirmBoxArrowBorderClass).css('top', $confirmBox.outerHeight()/2 - $arrowBorder.outerHeight()/2);
						
						topOffset = offset.top + $(trigger).outerHeight()/2 - $confirmBox.outerHeight()/2 + params.offset.top;
						leftOffset = offset.left - $confirmBox.outerWidth() - $arrowBorder.outerWidth() + params.offset.left;
						break;
				}
				
				// Make the confirm box appear right where it belongs
				$confirmBox.css({
					top: topOffset,
					left: leftOffset
				});
				
				$(trigger).data('fastconfirm_binded', true);
			}
		});
	};

})(jQuery);