<?php
/**
 * Allows plugin users to submit feedback using a partially pre-filled Google Form.
 *
 * @since   TBD
 *
 * @package TEC\Custom_Tables\V1\Feedback
 */

namespace TEC\Custom_Tables\V1\Feedback;

/**
 * Class Google_Form_Feedback
 *
 * @since   TBD
 *
 * @package TEC\Custom_Tables\V1\Feedback
 */
class Google_Form_Feedback implements Feedback_Interface {

	/**
	 * The URL of the form feedback should be submitted to.
	 *
	 * @since TBD
	 */
	const FORM_URL = 'https://docs.google.com/forms/d/e/1FAIpQLSfzTDl8ZpahmaV-7YjpB2dERoZpiJHA-cM8e-tdfZoma4jEkg/viewform?usp=pp_url';

	/**
	 * Renders the feedback prompt.
	 *
	 * @since TBD
	 */
	public function render_classic_editor_version() {
		?>
		<div class="notice notice-warning icaltec-feedback__container"
		     style="
		     background: #fff;
		     border: 1px solid #c3c4c7;
		     border-left-width: 4px;
		     border-left-color: #dba617;
		     box-shadow: 0 1px 1px rgba(0, 0, 0, 0.04);
		     padding: 1px 12px;"
		>
			<?php echo $this->get_notice_contents(); ?>
		</div>
		<?php
	}

	/**
	 * Returns the pre-filled form URL.
	 *
	 * @since TBD
	 *
	 * @return string
	 */
	private function get_form_prefilled_url() {
		return add_query_arg( $this->get_query_args(), self::FORM_URL );
	}

	/**
	 * Returns a map of the query arguments to apply to the Google Form URL for pre-filling.
	 *
	 * @since TBD
	 *
	 * @return array<string,string> A map from the pre-fill entry IDs to the pre-filled values.
	 */
	private function get_query_args() {
		global $wp_version;

		// This list is manually curated and will need to be updated when the Google Form is updated.
		$map = [
			'url'             => [
				'entry_id' => 'entry.1306235297',
				'value'    => home_url(),
			],
			'wp_version'      => [
				'entry_id' => 'entry.1235271646',
				'value'    => $wp_version,
			],
			'php_version'     => [
				'entry_id' => 'entry.23170971',
				'value'    => PHP_VERSION,
			],
			'os_version'      => [
				'entry_id' => 'entry.1707232830',
				'value'    => PHP_OS,
			],
			'icaltec_version' => [
				'entry_id' => 'entry.88103736',
				'value'    => TEC_CUSTOM_TABLES_VERSION,
			],
			'theme'           => [
				'entry_id' => 'entry.1216644610',
				'value'    => sprintf( 'template: %s, stylesheet: %s', get_template(), get_stylesheet() ),
			],
			'plugin_versions' => [
				'entry_id' => 'entry.915091913',
				'value'    => $this->compile_plugins_information(),
			],
			'multisite'       => [
				'entry_id' => 'entry.157607743',
				'value'    => is_multisite() ? 'Yes' : 'No',
			],
			'using_be'        => [
				'entry_id' => 'entry.2044173466',
				'value'    => tribe_get_option( 'toggle_blocks_editor' ) ? 'Yes' : 'No',
			],
			'using_views_v2'  => [
				'entry_id' => 'entry.1049970977',
				'value'    => tribe_get_option( 'views_v2_enabled' ) ? 'Yes' : 'No',
			],
		];

		return array_combine(
			array_column( $map, 'entry_id' ),
			array_column( $map, 'value' )
		);
	}

	/**
	 * Compiles the plugins information for the pre-filled submission.
	 *
	 * @since TBD
	 *
	 * @return string A list of the active plugins names and versions, not including
	 *                this one.
	 */
	private function compile_plugins_information() {
		$information = [];

		if( ! function_exists('get_plugin_data') ){
			require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
		}

		foreach ( wp_get_active_and_valid_plugins() as $plugin_file ) {
			if ( $plugin_file === TEC_CUSTOM_TABLES_V1_FILE ) {
				continue;
			}

			$plugin_data = get_plugin_data( $plugin_file );

			if ( ! isset( $plugin_data['Name'], $plugin_data['Version'] ) ) {
				continue;
			}

			$information[] = sprintf( '%s (v. %s)', $plugin_data['Name'], $plugin_data['Version'] );
		}

		return implode( '; ', $information );
	}

	/**
	 * Filters whole editor configuration, to localize information
	 * the Blocks Editor will be able to use.
	 *
	 * @since TBD
	 *
	 * @param array<string,mixed> $config A map of the current editor configuration, for all plugins.
	 *
	 * @return array<string,mixed> The modified plugin configuration.
	 */
	public function filter_editor_config( array $config = [] ) {
		if ( ! isset( $config['icaltec'] ) ) {
			$config['icaltec'] = [];
		}

		$config['icaltec']['feedbackNoticeText'] = $this->get_notice_contents();

		return $config;
	}

	/**
	 * Returns the HTML contents of the notice.
	 *
	 * Note: the content MUST be wrapped in an HTML to correctly render in the Blocks Editor context.
	 *
	 * @since TBD
	 *
	 * @return string The notice HTML contents.
	 */
	public function get_notice_contents() {
		return sprintf(
			'<p>Found an issue with Recurring Events + Series Alpha? <a target="_blank" href="%s">Let us know.</a></p>',
			$this->get_form_prefilled_url()
		);
	}
}