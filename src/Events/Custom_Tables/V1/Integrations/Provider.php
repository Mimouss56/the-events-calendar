<?php
/**
 * Provides the integrations required by the plugin to work with other plugins.
 *
 * @since   6.0.0
 *
 * @package TEC\Events\Custom_Tables\V1\Integrations
 */

namespace TEC\Events\Custom_Tables\V1\Integrations;


use TEC\Common\lucatume\DI52\ServiceProvider;


/**
 * Class Provider
 *
 * @since   6.0.0
 *
 * @package TEC\Events\Custom_Tables\V1\Integrations
 */
class Provider extends ServiceProvider {
	/**
	 * Registers the Service Providers required for the plugin to work with other plugins.
	 *
	 * @since 6.0.0
	 */
	public function register() {
		// Class defined by the Event Events plugin.
		if ( class_exists( '\\TEC\\Event_Tickets\\Custom_Tables\\V1\\Provider' ) ) {
			$this->container->register( \TEC\Tickets\Custom_Tables\V1\Provider::class );
		}
	}
}
