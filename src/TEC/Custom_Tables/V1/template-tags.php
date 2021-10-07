<?php
/**
 * The plugin template tags.
 *
 * @since   TBD
 *
 * @package iCalTec
 */

use TEC\Pro\Custom_Tables\V1\Models\Series_Relationship;
use TEC\Pro\Custom_Tables\V1\Series\Post_Type as Series;

/**
 * Whether a post is a valid Event Series or not.
 *
 * @since TBD
 *
 * @param int|WP_Post $post_id The post ID or object to check.
 *
 * @return bool Whether the post is an Event Series or not.
 */
function tribe_is_event_series( $post_id ) {
	return Series::POSTTYPE === get_post_type( $post_id );
	/*
	 * @todo add some model checks here
	return get_post_type( $post_id ) === Event_Series::POST_TYPE
	       && TEC\Custom_Tables\V1\Models\Event_Series::find_by_post_id($post_id) instanceof TEC\Custom_Tables\V1\Models\Event_Series;
	*/
}

/**
 * Return the first series associated with an event, if the event is private make sure to return `null` if the user
 * is not logged in.
 *
 * TODO: A more flexible approach to get the nth() series of an event or N series of an event.
 *
 * @since TBD
 *
 * @param int $event_post_id The ID of the post ID event we are looking for.
 *
 * @return WP_Post|null The post representing the series otherwise `null`
 */
function tec_event_series( $event_post_id ) {

	$relationship = Series_Relationship::where( 'event_post_id', $event_post_id )->first();

	if ( $relationship === null ) {
		return null;
	}

	$series = get_post( $relationship->series_post_id );

	if ( ! $series instanceof WP_Post ) {
		return null;
	}

	// Show private series only if the user is logged in.
	if ( 'private' === $series->post_status && is_user_logged_in() ) {
		return $series;
	}

	// Status considered invalid, meaning those post_status indicate a non relationship for public visibility.
	$invalid_status = [
		'draft'   => true,
		'pending' => true,
		'future'  => true,
		'trash'   => true,
	];

	if ( isset( $invalid_status[ $series->post_status ] ) ) {
		return null;
	}

	return $series;
}

/**
 * Determines if we should show the event title in the series marker.
 *
 * @since TBD
 *
 * @param Series|int|null  $series The post object or ID of the series the event belongs to.
 * @param WP_Post|int|null $event  The post object or ID of the event we're displaying.
 *
 * @return boolean
 */
function should_show_series_event_title( $series = null, $event = null ) {
	$show_title = false;
	if ( is_numeric( $series ) ) {
		$series = get_post( $series );
	}

	// If we have the series, check and see if the editor checkbox has been toggled.
	if ( ! empty( $series->ID ) ) {
		$show_title = get_post_meta( $series->ID, '_tec-series-show-title', true );
	}

	/**
	 * Allows .
	 *
	 * @TBD
	 *
	 * @param boolean          $show_title Should we (visually) hide the title.
	 * @param Series|int|null  $series The post object or ID of the series the event belongs to.
	 * @param WP_Post|int|null $event  The post object or ID of the event we're displaying.
	 */
	return apply_filters( 'tec_hide_series_marker_title', $show_title, $event, $series );
}

/**
 * Generates a list of classes for the marker title.
 *
 * @since TBD
 *
 * @param Series|int|null  $series The post object or ID of the series the event belongs to.
 * @param WP_Post|int|null $event  The post object or ID of the event we're displaying.
 *
 * @return array<string> $classes A list of classes for the marker title.
 */
function tec_get_series_marker_label_classes( $series = null, $event = null  ) {
	$classes    = [ 'tec_series_marker__title' ];

	/**
	 * If this returns false, we  hide the series marker event title.
	 * (via the `tribe-common-a11y-visual-hide` class which leaves the title for screen readers for additional context.)
	 */
	if ( ! should_show_series_event_title( $series, $event ) ) {
		$classes[] = 'tribe-common-a11y-visual-hide';
	}

	/**
	 * Allows filtering the series title classes.
	 *
	 * @TBD
	 *
	 * @param array<string> A list of classes to apply to the series title.
	 * @param Series|int|null  $series The post object or ID of the series the event belongs to.
	 * @param WP_Post|int|null $event  The post object or ID of the event we're displaying.
	 */
	return apply_filters( 'tec_series_marker_title_classes', $classes, $series, $event );
}