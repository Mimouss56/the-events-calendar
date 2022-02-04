<?php
/**
 * Modifies a query that is only fetching the Event post type to integrate with the plugin custom tables..
 *
 * @since   TBD
 *
 * @package TEC\Events\Custom_Tables\V1\WP_Query\Modifiers
 */

namespace TEC\Events\Custom_Tables\V1\WP_Query\Modifiers;

use TEC\Events\Custom_Tables\V1\Traits\With_WP_Query_Introspection;
use TEC\Events\Custom_Tables\V1\WP_Query\Custom_Tables_Query;
use Tribe__Events__Main as TEC;
use WP_Post;
use WP_Query;

/**
 * Class Events_Only_Modifier
 *
 * @since   TBD
 *
 * @package TEC\Events\Custom_Tables\V1\WP_Query\Modifiers
 */
class Events_Only_Modifier extends Base_Modifier {
	use With_WP_Query_Introspection;

	/**
	 * {@inheritDoc}
	 *
	 * @since TBD
	 *
	 * @param  WP_Query|null  $query
	 *
	 * @return bool
	 */
	public function applies_to( WP_Query $query = null ) {
		return $query instanceof WP_Query
		       && ! $query instanceof Custom_Tables_Query
		       && $this->is_query_for_post_type( $query, TEC::POSTTYPE )
		       && ! is_admin();
	}

	/**
	 * {@inheritDoc}
	 *
	 * @since TBD
	 */
	public function hook() {
		add_filter( 'posts_pre_query', [ $this, 'filter_posts_pre_query' ], 100, 2 );
	}

	/**
	 * Pre-fills the query posts with results fetched from the custom tables.
	 *
	 * @since TBD
	 *
	 * @param WP_Query|null           $wp_query    A reference to the `WP_Query` instance that is currently running.
	 * @param array<WP_Post|int>|null $posts       The filter input value, it could have already be filtered by other
	 *                                             plugins at this stage.
	 *
	 * @return null|array<WP_Post|int> The filtered value of the posts, injected before the query actually runs.
	 */
	public function filter_posts_pre_query( $posts = null, WP_Query $wp_query = null ) {
		if ( ! $this->is_target_query( $wp_query ) ) {
			return $posts;
		}

		if ( null !== $posts ) {
			$this->unhook();

			// If something already intervened in the filter, then bail and do not touch the query at all.
			return $posts;
		}

		$query = Custom_Tables_Query::from_wp_query( $wp_query );

		$posts = $query->get_posts();

		$posts = array_filter( $posts , static function( $post) {
			$id = $post instanceof WP_Post ? $post->ID : $post;
			return $id > 0;
		} );

		$this->done_filters[ current_filter() ] = array_map( 'get_post', $posts );

		$this->done();

		return $posts;
	}

	/**
	 * {@inheritDoc}
	 *
	 * @since TBD
	 */
	public function unhook() {
		remove_filter( 'posts_pre_query', [ $this, 'filter_posts_pre_query' ], 100 );
	}
}
