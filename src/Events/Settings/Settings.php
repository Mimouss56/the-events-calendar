<?php
/**
 * Handles injecting Settings.
 *
 * @since TBD
 *
 * @package TEC\Events\Settings
 */

namespace TEC\Events\Settings;

/**
 * Class Settings
 *
 * @since TBD
 *
 * @package TEC\Events\Settings
 */
class Settings {
	public $option_group = 'tec_events';

	public function add_menu() {
		tribe( 'tec.settings.generator' )->add_menu(
			_x( 'TEC Events Admin', 'The title for the Events admin page', 'the-events-calendar' ),
			_x( 'TEC Events', 'The title for the Events admin menu', 'the-events-calendar' ),
			'edit_tribe_events',
			'tec_events',
			'', // callback
			$this->get_menu_icon(), // icon url
			5
		);
	}

	public function add_settings_page() {
		tribe( 'tec.settings.generator' )->add_settings_page(

		);
	}

	public function add_settings() {
		$settings_file = plugin_dir_path( __FILE__ ) . 'Data.php';

		tribe( 'tec.settings.generator' )->add_settings( $this->option_group, $settings_file );
	}

	/**
	 * Get the icon for The Events Calendar menu item.
	 *
	 * @since 5.15.0
	 * @since TBD copied to TEC_Events menu.
	 *
	 * @return string
	 */
	public function get_menu_icon() {
		$icon = 'data:image/svg+xml;base64,' . base64_encode( '<svg xmlns="http://www.w3.org/2000/svg" fill="#9ba2a6" viewBox="0 0 13.24 15.4"><defs><style>.cls-1{fill-rule:evenodd;}</style></defs><g id="Layer_2" data-name="Layer 2"><g id="Layer_1-2" data-name="Layer 1"><path class="cls-1" d="M12.89,6.1,11.54,2.2l0-.06A1.85,1.85,0,0,0,9.14,1.06l-.73.26L8,.29v0A.45.45,0,0,0,7.47,0a.43.43,0,0,0-.25.23A.45.45,0,0,0,7.2.6l.37,1L2.75,3.39l-.36-1,0-.05a.44.44,0,0,0-.56-.22.44.44,0,0,0-.26.57l.36,1L1.17,4A1.86,1.86,0,0,0,.11,6.33L3.19,15a.66.66,0,0,0,.61.4.59.59,0,0,0,.23,0L7.4,14.13l0,0,.1,0a5,5,0,0,0,2-2.47c.11-.24.21-.49.31-.77l.27-.72.07-.19a4.3,4.3,0,0,0,2-.39,3.13,3.13,0,0,1-1.72,2.3.43.43,0,0,0-.25.23.45.45,0,0,0,0,.34.42.42,0,0,0,.23.26.45.45,0,0,0,.34,0C13.13,11.87,13.72,8.64,12.89,6.1Zm-.56,1.81a.79.79,0,0,1-.25.58A2.85,2.85,0,0,1,10,9c-.39,0-.51.22-.68.67L9,10.52c-.1.26-.19.49-.29.71a4.32,4.32,0,0,1-1.59,2L3.94,14.44,1.7,8.12l9.74-3.46.63,1.82a5.11,5.11,0,0,1,.26,1.35V7.9Z"/></g></g></svg>' );

		/**
		 * Filter the menu icon for The Events Calendar in the WordPress admin.
		 *
		 * @since 5.15.0
		 *
		 * @param string $icon The menu icon for The Events Calendar in the WordPress admin.
		 */
		return apply_filters( 'tec_events_menu_icon', $icon );
	}
}
