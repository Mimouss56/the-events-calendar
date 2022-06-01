<?php
/**
 * A value object providing information about an Event migration.
 *
 * @since   TBD
 * @package TEC\Events\Custom_Tables\V1\Migration\Reports;
 */

namespace TEC\Events\Custom_Tables\V1\Migration\Reports;

use TEC\Events\Custom_Tables\V1\Migration\String_Dictionary;
use TEC\Events\Custom_Tables\V1\Migration\Strategies\Single_Event_Migration_Strategy;


class Event_Report_Categories {

	/**
	 * @var String_Dictionary Translations object.
	 */
	protected $text;

	/**
	 * @since TBD
	 *
	 * @param String_Dictionary $text The translations object.
	 */
	public function __construct( String_Dictionary $text ) {
		$this->text = $text;
	}

	/**
	 * Retrieve the migration event report categories.
	 *
	 * @since TBD
	 *
	 * @return array<array{ key:string, label:string }>
	 */
	public function get_categories() {
		$defaults = [
			[
				'key'   => Single_Event_Migration_Strategy::get_slug(),
				'label' => $this->text->get( 'migration-prompt-strategy-' . Single_Event_Migration_Strategy::get_slug() )
			]
		];

		/**
		 * The shape of the event report categories. The individual events reported are listed inside their respective category.
		 * Take note on the key, it is important and used in several areas to fetch and report the event report details. The
		 * order matters for the templates, this is the display order. Sort accordingly.
		 *
		 * @since TBD
		 *
		 * @param array<array{ key:string, label:string }> $defaults The default TEC migration event report categories.
		 * @param String_Dictionary Translations object
		 */
		return apply_filters( 'tec_events_custom_tables_v1_migration_event_report_categories', $defaults, $this->text );
	}

}