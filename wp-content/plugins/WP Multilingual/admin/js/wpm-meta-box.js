/**
 * Translation Meta Box Script
 */

(function($) {
	'use strict';

	$(document).ready(function() {
		var $postLangSelect = $('#wpm_post_language');
		if (!$postLangSelect.length) {
			return;
		}

		var initialLang = $postLangSelect.val();

		$postLangSelect.on('change', function() {
			var newLang = $(this).val();
			var $wrap = $(this).closest('.wpm-meta-box-wrap');
			var postId = $wrap.data('post-id');

			// If changing language on an existing post, provide clear UI feedback
			if (postId && newLang !== initialLang) {
				if (!$('#wpm_lang_changed_notice').length) {
					$postLangSelect.after('<p id="wpm_lang_changed_notice" class="description" style="color:#d97706; margin-top:4px; font-size:12px;">⚠️ Language selection changed. Update the post to save changes.</p>');
				}
			} else {
				$('#wpm_lang_changed_notice').remove();
			}
		});
	});

})(jQuery);
