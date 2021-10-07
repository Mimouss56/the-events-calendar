<?php
/**
 * Validates an End Date UTC input.
 *
 * @since   TBD
 *
 * @package TEC\Custom_Tables\V1\Models\Validators
 */

namespace TEC\Custom_Tables\V1\Models\Validators;

use TEC\Custom_Tables\V1\Models\Model;
use Tribe__Timezones as Timezones;

/**
 * Class Valid_Timezone
 *
 * @since   TBD
 *
 * @package TEC\Custom_Tables\V1\Models\Validators
 */
class Valid_Timezone extends Validation {
	/**
	 * {@inheritDoc}
	 */
	public function validate( Model $model, $name, $value ) {
		// The value is already a timezone object.
		if ( $value instanceof \DateTimeZone ) {
			return true;
		}

		$is_valid_timezone = Timezones::is_valid_timezone( $value );

		if ( ! $is_valid_timezone ) {
			$this->error_message = 'The provided timezone is not a valid timezone.';
		}

		return $is_valid_timezone;
	}
}