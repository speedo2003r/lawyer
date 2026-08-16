/**
 * Language Switcher Block Editor Component (Vanilla JS for standard WP without build-step required)
 */

(function (wp) {
	var el = wp.element.createElement;
	var __ = wp.i18n.__;
	var InspectorControls = wp.blockEditor.InspectorControls || wp.editor.InspectorControls;
	var PanelBody = wp.components.PanelBody;
	var SelectControl = wp.components.SelectControl;
	var ToggleControl = wp.components.ToggleControl;

	wp.blocks.registerBlockType('wpm/language-switcher', {
		edit: function (props) {
			var attributes = props.attributes;
			var setAttributes = props.setAttributes;

			return el(
				wp.element.Fragment,
				null,
				el(
					InspectorControls,
					null,
					el(
						PanelBody,
						{ title: __('Switcher Settings', 'wp-multilingual'), initialOpen: true },
						el(SelectControl, {
							label: __('Display Type', 'wp-multilingual'),
							value: attributes.type,
							options: [
								{ label: __('List', 'wp-multilingual'), value: 'list' },
								{ label: __('Dropdown', 'wp-multilingual'), value: 'dropdown' }
							],
							onChange: function (val) {
								setAttributes({ type: val });
							}
						}),
						el(ToggleControl, {
							label: __('Show Flags', 'wp-multilingual'),
							checked: attributes.showFlags,
							onChange: function (val) {
								setAttributes({ showFlags: val });
							}
						}),
						el(ToggleControl, {
							label: __('Show Language Names', 'wp-multilingual'),
							checked: attributes.showNames,
							onChange: function (val) {
								setAttributes({ showNames: val });
							}
						}),
						el(ToggleControl, {
							label: __('Show Native Names', 'wp-multilingual'),
							checked: attributes.showNativeNames,
							onChange: function (val) {
								setAttributes({ showNativeNames: val });
							}
						}),
						el(ToggleControl, {
							label: __('Only Show Available Translations', 'wp-multilingual'),
							checked: attributes.onlyWithTranslations,
							onChange: function (val) {
								setAttributes({ onlyWithTranslations: val });
							}
						})
					)
				),
				el(
					'div',
					{ className: 'wpm-block-preview' },
					el('span', { className: 'dashicons dashicons-translation', style: { marginRight: '8px' } }),
					el('strong', null, __('Language Switcher Preview', 'wp-multilingual')),
					el('p', { className: 'description', style: { margin: '4px 0 0 0', fontSize: '12px' } },
						__('Outputs the dynamic multilingual language switcher based on configured languages.', 'wp-multilingual')
					)
				)
			);
		},
		save: function () {
			// Dynamic block rendered via PHP server-side render callback
			return null;
		}
	});
})(window.wp);
