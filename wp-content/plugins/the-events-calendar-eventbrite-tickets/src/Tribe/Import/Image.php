<?php

namespace Tribe\Events\Eventbrite\Import;

use Tribe__Events__Aggregator__Record__Activity;
use Tribe__Process__Post_Thumbnail_Setter;

/**
 * Class Image
 *
 * @since   TBD
 *
 * @package Tribe\Events\Eventbrite\Import
 */
class Image {
	/**
	 * Attach hooks
	 *
	 * @since  TBD
	 */
	public function hook() {
		add_filter( 'tribe_aggregator_import_event_image', [ $this, 'process_images' ], 10, 3 );
		add_filter( 'tribe_process_handlers', [ $this, 'register_handler' ] );
	}

	/**
	 * Prevent to process images on sync way, capture the filter and process eventbrite images on the background.
	 *
	 * @since TBD
	 *
	 * @param  $import_event_image bool If the import should be short circuit.
	 * @param  $event              array An array representing the inserted event into the site.
	 * @param  $activity           Tribe__Events__Aggregator__Record__Activity Keep track of the activity from this import.
	 *
	 * @return bool If the import should be short circuit or not.
	 */
	public function process_images( $import_event_image, $event, $activity ) {
		// This image is filter for other plugin or source  don't act on that.
		if ( ! $import_event_image ) {
			return $import_event_image;
		}

		// No image or event is present for this record.
		if ( empty( $event['ID'] ) || empty( $event['image'] ) ) {
			return $import_event_image;
		}

		// This is not an eventbrite event so nothing to do here.
		if ( empty( $event['EventBriteID'] ) ) {
			return $import_event_image;
		}


		$image_url = $event['image'];
		if ( is_object( $event['image'] ) ) {
			if ( $event['image']->id ) {
				$activity->add( 'attachment', 'scheduled', $event['ID'] );
				( new Featured_Image() )->set_image_id( $event['image']->id )->set_event_id( $event['ID'] )->dispatch();
			} else {
				do_action(
					'tribe_log',
					'debug',
					__METHOD__,
					[
						'message' => 'A valid image ID was not defined',
						'event'   => $event,
					]
				);
			}

			return false;
		}

		$activity->add( 'attachment', 'scheduled', $event['ID'] );

		$post_thumbnail_process = new Tribe__Process__Post_Thumbnail_Setter();
		$post_thumbnail_process->set_post_id( $event['ID'] );
		$post_thumbnail_process->set_post_thumbnail( $image_url );
		$post_thumbnail_process->dispatch();

		// Don't process the images we are taking care of those in the background.
		return false;
	}

	/**
	 * Register the handler to process Event Thumbnail creation on the background-
	 *
	 * @since  TBD
	 *
	 * @param  $handlers array An array with all the different handlers for background processing.
	 *
	 * @return mixed
	 */
	public function register_handler( $handlers ) {
		$handlers[] = Featured_Image::class;

		return $handlers;
	}
}
