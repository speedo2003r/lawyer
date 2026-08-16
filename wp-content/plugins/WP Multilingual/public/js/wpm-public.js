/**
 * WP Multilingual Public JS
 */

(function() {
	'use strict';

	// Any lightweight client-side language switcher events if needed
	document.addEventListener('DOMContentLoaded', function() {
		var selects = document.querySelectorAll('.wpm-dropdown-select');
		selects.forEach(function(select) {
			select.addEventListener('change', function() {
				if (this.value) {
					window.location.href = this.value;
				}
			});
		});
	});

})();
