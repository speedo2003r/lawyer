/**
 * WP Multilingual Admin Scripts
 */

(function($) {
	'use strict';

	$(document).ready(function() {

		// Preset language auto-fill
		$('#wpm_preset_select').on('change', function() {
			var $opt = $(this).find(':selected');
			if (!$opt.val()) {
				return;
			}

			$('#wpm_name').val($opt.data('name'));
			$('#wpm_native_name').val($opt.data('native'));
			$('#wpm_code').val($opt.val());
			$('#wpm_locale').val($opt.data('locale'));
			$('#wpm_url_code').val($opt.data('url'));
			$('#wpm_flag').val($opt.data('flag'));
			$('#wpm_direction').val($opt.data('direction'));
		});

		// Bulk assign default language to existing posts
		$('#wpm_assign_bulk_btn').on('click', function(e) {
			e.preventDefault();
			var $btn = $(this);
			var $status = $('#wpm_bulk_status');

			$btn.prop('disabled', true);
			$status.text('Processing...');

			$.post(wpmAdmin.ajaxUrl, {
				action: 'wpm_assign_default_language_bulk',
				nonce: wpmAdmin.nonce
			}, function(response) {
				$btn.prop('disabled', false);
				if (response.success) {
					$status.css('color', '#16a34a').text(response.data.message);
				} else {
					$status.css('color', '#dc2626').text(response.data.message || 'Error occurred.');
				}
			}).fail(function() {
				$btn.prop('disabled', false);
				$status.css('color', '#dc2626').text('Request failed.');
			});
		});

		// Create translation button (from meta box or post list table)
		$(document).on('click', '.wpm-btn-create-trans', function(e) {
			e.preventDefault();
			var $btn = $(this);
			var sourceId = $btn.data('source-id');
			var targetLang = $btn.data('target-lang');
			var $chk = $('.wpm-chk-duplicate[data-lang="' + targetLang + '"]');
			var duplicate = $chk.length ? ($chk.is(':checked') ? 1 : 0) : 1;

			$btn.prop('disabled', true).addClass('wpm-loading');

			$.post(wpmAdmin.ajaxUrl, {
				action: 'wpm_create_translation',
				nonce: wpmAdmin.nonce,
				source_id: sourceId,
				target_lang: targetLang,
				duplicate_content: duplicate
			}, function(response) {
				if (response.success && response.data.edit_url) {
					window.location.href = response.data.edit_url;
				} else {
					alert(response.data.message || 'Failed to create translation.');
					$btn.prop('disabled', false).removeClass('wpm-loading');
				}
			}).fail(function() {
				alert('AJAX request failed.');
				$btn.prop('disabled', false).removeClass('wpm-loading');
			});
		});

		// Unlink translation button
		$(document).on('click', '.wpm-btn-unlink', function(e) {
			e.preventDefault();
			if (!confirm(wpmAdmin.unlinking)) {
				return;
			}

			var $btn = $(this);
			var postId = $btn.data('post-id');

			$btn.prop('disabled', true);

			$.post(wpmAdmin.ajaxUrl, {
				action: 'wpm_unlink_translation',
				nonce: wpmAdmin.nonce,
				post_id: postId
			}, function(response) {
				if (response.success) {
					window.location.reload();
				} else {
					alert(response.data.message || 'Failed to unlink.');
					$btn.prop('disabled', false);
				}
			}).fail(function() {
				alert('AJAX request failed.');
				$btn.prop('disabled', false);
			});
		});

	});

})(jQuery);
