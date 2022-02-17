<?php
/**
 * Acts on WordPress update flow phases to update the Events and their custom tables data.
 *
 * @since   TBD
 *
 * @package TEC\Events_Pro\Updates
 */

namespace TEC\Events\Custom_Tables\V1\Updates;

use TEC\Events\Custom_Tables\V1\Models\Occurrence;
use Tribe__Events__Main as TEC;
use WP_Post;
use WP_REST_Request;

/**
 * Class Controller
 *
 * @since   TBD
 *
 * @package TEC\Events\Custom_Tables\V1\Updates
 */
class Controller {

	/**
	 * A reference to the current Meta Watcher service implementation.
	 *
	 * @since TBD
	 *
	 * @var Meta_Watcher
	 */
	private $meta_watcher;
	/**
	 * A reference to the current Request Factory implementation.
	 *
	 * @since TBD
	 *
	 * @var Requests
	 */
	private $requests;
	/**
	 * A reference to the Event, and related models, repository.
	 *
	 * @since TBD
	 *
	 * @var Events
	 */
	private $events;

	/**
	 * Controller constructor.
	 *
	 * @since TBD
	 *
	 * @param Meta_Watcher $meta_watcher A reference to the current Meta Watcher service implementation.
	 * @param Requests     $requests     A reference to the curret Request factory and repository implementation.
	 * @param Events       $events       A reference to the current Events implementation.
	 */
	public function __construct( Meta_Watcher $meta_watcher, Requests $requests, Events $events ) {
		$this->meta_watcher = $meta_watcher;
		$this->requests     = $requests;
		$this->events       = $events;
	}

	/**
	 * Updates the custom tables' information for each Event post whose important
	 * meta was updated during the request.
	 *
	 * @since TBD
	 *
	 * @return int The number of updated Events.
	 */
	public function commit_updates() {
		if ( empty( $this->meta_watcher->get_marked_ids() ) ) {
			return 0;
		}

		$request = $this->requests->from_http_request();

		$updated = 0;
		foreach ( $this->meta_watcher->get_marked_ids() as $booked_id ) {
			$updated += $this->commit_post_updates( $booked_id, $request );
		}

		return $updated;
	}

	/**
	 * Updates the custom tables' information for an Event post whose important
	 * meta was updated.
	 *
	 * After a first update, the post ID is removed from the marked-for-update stack
	 * and will not be automatically updated again during the request.
	 *
	 * @since TBD
	 *
	 * @param WP_REST_Request|null $request A reference to the object modeling the current request,
	 *                                      or `null` to build a request from the current HTTP data.
	 *                                      Mind the WP_REST_Request class can be used to
	 *                                      model a non-REST API request too|
	 *
	 * @param int                  $post_id The post ID, not guaranteed to be an Event post ID if this
	 *                                      method is not called from this class!
	 *
	 * @return bool Whether the post updates were correctly applied or not.
	 */
	public function commit_post_updates( $post_id, WP_REST_Request $request = null ) {
		if ( null === $request ) {
			$request = $this->requests->from_http_request();
		}

		if ( ! $this->should_update_custom_tables( $post_id, $request ) ) {
			// The post relevant meta was not changed, do nothing.
			return false;
		}

		$updated = $this->update_custom_tables( $post_id, $request );

		if ( $updated ) {
			// Remove the post ID from the list of post IDs still to update.
			$this->meta_watcher->remove( $post_id );
		}

		$this->events->rebuild_known_range();

		return true;
	}

	/**
	 * Checks if we are watching a meta key or leverage the filter to see if other situation to update CT.
	 *
	 * @since TBD
	 *
	 * @param WP_REST_Request $request A reference to the Request object modeling the context of the check.
	 * @param int             $post_id The ID of the post the check is being made for.
	 *
	 * @return bool Whether custom tables should be updated or not.
	 */
	private function should_update_custom_tables( $post_id, WP_REST_Request $request ) {
		$update = $this->meta_watcher->is_tracked( $post_id );

		/**
		 * Filters whether custom tables should be updated or not after the default logic
		 * has run.
		 *
		 * @since TBD
		 *
		 * @param bool            $update        Whether the custom tables should be updated or not, by
		 *                                       default, the initial value will be based on whether a
		 *                                       post relevant and watched meta was updated or not.
		 * @param int             $post_id       The ID of the post that is currently being updated.
		 * @param WP_REST_Request $request       A reference to the object modeling the current request.
		 */
		return apply_filters( 'tec_events_custom_tables_v1_should_update_custom_tables', $update, $post_id, $request );
	}

	/**
	 * Updates the custom tables with the data for an Event post.
	 *
	 * @since TBD
	 *
	 * @param int             $post_id The Even post ID.
	 * @param WP_REST_Request $request A reference to the request object triggering the update.
	 *
	 * @return bool Whether the update was successful or not.
	 */
	private function update_custom_tables( $post_id, WP_REST_Request $request ) {
		/**
		 * Fires before an Event custom tables data is updated.
		 *
		 * @since TBD
		 *
		 * @param int             $post_id The post ID of the Event being updated.
		 * @param WP_REST_Request $request A reference to the request object triggering the update.
		 */
		do_action( 'tec_events_custom_tables_v1_update_post_before', $post_id, $request );

		/**
		 * Fires before the default The Events Calendar logic to update an Event custom tables
		 * information is applied.
		 * Returning a non `null` value from this filter will prevent the default logic from running.
		 *
		 * @since TBD
		 *
		 * @param mixed|null      $updated      Whether the post custom tables information was updated by any
		 *                                      filtering function or not. If a non `null` value is returned
		 *                                      from this filter, then the default logic will not be applied.
		 * @param int             $post_id      The post ID of the Event whose custom tables information should be
		 *                                      updated.
		 * @param WP_REST_Request $request      A reference to the object modeling the current request,
		 *                                      if any. Mind the WP_REST_Request class can be used to
		 *                                      model a non-REST API request too!
		 *
		 * @return bool|null Whether the custom tables' updates were correctly applied or not.
		 */
		$updated = apply_filters( 'tec_events_custom_tables_v1_commit_post_updates', null, $post_id, $request );

		if ( null === $updated ) {
			// Apply the default logic.
			$updated = $this->events->update( $post_id );
		}

		/**
		 * Filters whether an Event custom tables data has been correctly updated or not.
		 *
		 * @since TBD
		 *
		 * @param bool            $updated Whether the previous update operation was correctly performed or not.
		 * @param int             $post_id The post ID of the Event being updated.
		 * @param WP_REST_Request $request A reference to the request object triggering the update.
		 */
		$updated = apply_filters( 'tec_events_custom_tables_v1_updated_post', $updated, $post_id, $request );

		/**
		 * Fires after an Event custom tables data is updated.
		 *
		 * @since TBD
		 *
		 * @param int             $post_id The post ID of the Event being updated.
		 * @param WP_REST_Request $request A reference to the request object triggering the update.
		 */
		do_action( 'tec_events_custom_tables_v1_update_post_after', $post_id, $request );

		return $updated;
	}

	/**
	 * Updates the custom tables' information for an Event post whose important meta
	 * was updated in the context of a REST request.
	 *
	 * After a first update, the post ID is removed from the marked-for-update stack
	 * and will not be automatically updated again during the request.
	 *
	 * @since TBD
	 *
	 * @param WP_Post         $post    A reference to the post object representing the Event
	 *                                 post.
	 * @param WP_REST_Request $request A reference to the REST API request object that is,
	 *                                 currently, being processed.
	 *
	 * @return bool Whether the custom tables' updates were correctly applied or not.
	 */
	public function commit_post_rest_update( WP_Post $post, WP_REST_Request $request ) {
		if ( ! $this->meta_watcher->is_tracked( $post->ID ) ) {
			return false;
		}

		return $this->commit_post_updates( $post->ID, $request );
	}

	/**
	 * Deletes an Event custom tables information.
	 *
	 * @since TBD
	 *
	 * @param int                  $post_id The deleted Event post ID.
	 * @param WP_REST_Request|null $request A reference to the request object triggering the deletion, if any.
	 *
	 * @return int|false Either the number of affected rows, or `false` on failure.
	 */
	public function delete_custom_tables_data( $post_id, WP_REST_Request $request = null ) {
		if ( null === $request ) {
			$request = $this->requests->from_http_request();
		}

		/**
		 * Fires before an Event custom tables data is removed.
		 *
		 * By the time this action fires, the Event post has not yet been removed from
		 * the WordPress posts tables.
		 *
		 * @since TBD
		 *
		 * @param int             $post_id The Event post ID.
		 * @param WP_REST_Request $request A reference to the request object triggering the update.
		 */
		do_action( 'tec_events_custom_tables_v1_delete_post', $post_id, $request );

		$affected = $this->events->delete( $post_id );

		/**
		 * Fires after the Event custom tables data has been removed from the database.
		 *
		 * By the time this action fires, the Event post has not yet been removed from
		 * the WordPress posts tables.
		 *
		 * @since TBD
		 *
		 * @param int             $affected The number of affected rows, across all custom tables.
		 *                                  Keep in mind db-level deletions will not be counted in
		 *                                  this value!
		 * @param int             $post_id  The Event post ID.
		 * @param WP_REST_Request $request  A reference to the request object triggering the update.
		 */
		return apply_filters( 'tec_events_custom_tables_v1_deleted_post', $affected, $post_id, $request );
	}

	/**
	 * Filters the location a post should be redirected to.
	 *
	 * @since TBD
	 *
	 * @param string $location The post redirection location, as worked out
	 *                         by WordPress and previous filtering methods.
	 * @param int $post_id The
	 *
	 * @return mixed|void
	 */
	public function redirect_post_location( $location, $post_id ){
		if ( TEC::POSTTYPE !== get_post_type( $post_id ) ) {
			return $location;
		}

		/**
		 * Filters the location the Event post should be redirected to in the context of a Classic Editor
		 * Request.
		 *
		 * The Events Calendar plugin will not redirect the post location, by default.
		 *
		 * @since TBD
		 *
		 * @param string $location The original location to redirect the post provided by WordPress
		 *                         and filtered by other intervening methods.
		 * @param int    $post_id  The post ID to redirect the location for.
		 */
		return apply_filters( 'tec_events_custom_tables_v1_redirect_post_location', $location, $post_id );
	}
}