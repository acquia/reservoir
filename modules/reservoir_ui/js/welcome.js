/**
 * @file
 * Triggers the tour immediately after installation.
 *
 * Ridiculously hacky, but gets the job done for now :)
 */

(function (domready, jQuery) {

  'use strict';

  domready(function () {
    if (jQuery('.messages:contains("Congratulations, you installed Reservoir!")').length) {
      document.querySelector('#toolbar-tab-tour button').click();
    }
  });

})(domready, jQuery);
