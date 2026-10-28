/**
 * @file
 * Fullcalendar Tooltip plugin JavaScript file.
 *
 * Custom tooltip plugin for FullCalendar.
 * More options see:
 * http://qtip2.com/options.
 */

(function ($) {
    $.fn.fullCalendarTooltip = function (title, description) {
		 
		// 2019-12-30T23:00:00
		var desc = "";
		if(description.length == 19){
				desc = description.substring(8,10)+"/"+description.substring(5,7)+"/"+description.substring(0,4)+" "+description.substring(11,16);	
		}
		console.log(description+" --> "+desc);
        if (typeof this.qtip === 'function') {
             this.qtip({
                    content: {
                      text: desc,
                      title: title,
                  },
                  position: {
                      my: 'bottom center',  // Position my bottom center...
                      at: 'top center', // at the top center of...
                  },
                  style: {
                      classes: 'qtip-bootstrap qtip-shadow'
                  }
             });
        }
        return this;
    };
}
)(jQuery);
