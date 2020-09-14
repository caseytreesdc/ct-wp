<?php

/**
 * EventBrite EA connector
 *
 * @since 4.5
 */
class Tribe__Events__Tickets__Eventbrite__Aggregator {

	/**
	 * Hooks all the Filters and Actions for this Singleton
	 *
	 * @since 4.5
	 *
	 * @return void
	 */
	public function hook() {
		add_filter( 'tribe_aggregator_service_post_pue_licenses', [ $this, 'filter_add_pue_key' ], 15, 2 );
		add_filter( 'tribe_aggregator_service_put_pue_licenses', [ $this, 'filter_add_pue_key' ], 15, 2 );

		add_action( 'tribe_aggregator_after_insert_post', [ $this, 'save_event_meta' ], 15, 3 );

		add_filter( 'tribe_aggregator_api', [ $this, 'filter_fake_pue_key' ] );

		add_filter( 'tribe_aggregator_eventbrite_token_callback_args', [ $this, 'filter_add_token_callback_license' ] );

		add_filter( 'tribe_aggregator_get_import_data_args', [ $this, 'filter_add_license_get_import_data' ], 10, 2 );
		add_filter( 'tribe_aggregator_get_image_data_args', [ $this, 'filter_add_license_get_import_data' ], 10, 2 );

		add_action( 'tribe_aggregator_after_insert_posts', [ $this, 'save_callback' ], 10, 3 );
	}

	/**
	 * Adds a fake PUE key to trick EA into thinking it's active
	 *
	 * @since 4.5
	 *
	 * @param object $api Aggregator API object
	 *
	 * @return object
	 */
	public function filter_fake_pue_key( $api ) {
		$api->licenses = $this->filter_add_pue_key( isset( $this->licenses ) ? $this->licenses : [] );

		return $api;
	}

	/**
	 * Adds a fake PUE key to trick EA into thinking it's active
	 *
	 * @since 4.5
	 *
	 * @param array $api Aggregator API object
	 *
	 * @return array
	 */
	public function filter_add_token_callback_license( $args ) {
		$args['licenses'] = $this->filter_add_pue_key( isset( $args['licenses'] ) ? $args['licenses'] : [] );
		$args['origin']   = 'eventbrite';

		return $args;
	}

	/**
	 * Adds a fake PUE key to trick EA into thinking it's active
	 *
	 * @since 4.5
	 *
	 * @param array $api Aggregator API object
	 *
	 * @return array
	 */
	public function filter_add_license_get_import_data( $args, $record ) {
		if ( ! isset( $record->meta['origin'] ) || 'eventbrite' !== $record->meta['origin'] ) {
			return $args;
		}

		$args['licenses'] = $this->filter_add_pue_key( isset( $args['licenses'] ) ? $args['licenses'] : [] );
		$args['origin']   = 'eventbrite';

		return $args;
	}

	/**
	 * Adds the PUE key to the Queue of Import records for Eventbrite
	 *
	 * @since 4.5
	 *
	 * @param array $licenses Existing licenses
	 * @param array $args     Which arguments are going to be used on the post request
	 *
	 * @return array
	 */
	public function filter_add_pue_key( $licenses = [], $args = [ 'origin' => 'eventbrite' ] ) {
		$licenses = (array) $licenses;

		if ( ! isset( $args['origin'] ) && 'eventbrite' !== $args['origin'] ) {
			return $licenses;
		}

		$license = tribe( 'eventbrite.pue' )->pue_instance->get_key();

		if ( empty( $license ) ) {
			return $licenses;
		}

		// Add the EB license to the mix
		$licenses['tribe-eventbrite'] = $license;

		return $licenses;
	}

	/**
	 * Saves the Event Meta for Eventbrite
	 *
	 * @since 4.5
	 *
	 * @param array|WP_Post                               $event  Which Event data was sent.
	 * @param array                                       $item   Raw version of the data sent from EA.
	 * @param Tribe__Events__Aggregator__Record__Abstract $record The record we are dealing with.
	 *
	 * @return array|bool
	 */
	public function save_event_meta( $event, $item, $record ) {
		if ( ! $event instanceof WP_Post && empty( $event['eventbrite'] ) ) {
			return false;
		}

		if ( ! $event instanceof WP_Post ) {
			$event_id   = $event['ID'];
			$eventbrite = $event['eventbrite'];

			//setup object fields to use existing naming
			$eventbrite->id             = empty( $event['EventBriteID'] ) ? null : tribe( 'eventbrite.sync.utilities' )->sanitize_absint( $event['EventBriteID'] );
			$eventbrite->url            = empty( $event['EventURL'] ) ? null : esc_url( $event['EventURL'] );
			$eventbrite->ticket_classes = empty( $eventbrite->tickets ) ? null : $eventbrite->tickets;
			unset( $eventbrite->tickets );
		} else {
			$event_id                   = $event->ID;
			$eventbrite                 = $item->eventbrite;
			$eventbrite->url            = empty( $item->url ) ? null : esc_url( $item->url );
			$eventbrite->id             = $item->eventbrite_id;
			$eventbrite->ticket_classes = empty( $item->eventbrite->tickets ) ? null : $item->eventbrite->tickets;

			unset( $eventbrite->tickets );
		}

		$eventbrite->is_owner = empty( $item->is_owner ) ? null : $item->is_owner;

		// Update Eventbrite privacy setting
		tribe( 'eventbrite.sync.event' )->set_event_privacy_meta( $event_id, $eventbrite );

		// set local status
		$current_status = ( ! empty( $eventbrite->status ) ? esc_attr( $eventbrite->status ) : 'draft' );
		update_post_meta( $event_id, '_EventBriteStatus', $current_status );

		// local Eventbrite setting to show tickets
		$show_tickets = get_post_meta( $event_id, '_EventShowTickets', true );
		$show_tickets = ( ! empty( $show_tickets ) ? esc_attr( $show_tickets ) : 'yes' );
		update_post_meta( $event_id, '_EventShowTickets', $show_tickets );

		// local Eventbrite setting to connect event to eventbrite
		update_post_meta( $event_id, '_EventRegister', 'yes' );

		// save object
		$saved['tickets'] = update_post_meta( $event_id, tribe( 'eventbrite.event' )->key_tickets, $eventbrite );

		return $saved;
	}

	/**
	 * Update the EA record, once the import has been completed, only when the import was done from "your account"
	 * and only for Eventbrite. A call is made back to EA to register a callback associated with this import.
	 *
	 * @since TBD
	 *
	 * @param array                                       $items    An array of items to insert.
	 * @param array                                       $meta     The record meta information.
	 * @param Tribe__Events__Aggregator__Record__Activity $activity The record insertion activity repo.
	 */
	public function save_callback( $items, $meta, $activity ) {
		if (
			empty( $meta['preview'] )
			|| empty( $meta['import_id'] )
			|| empty( $meta['finalized'] )
			|| empty( $meta['origin'] )
			|| empty( $meta['source'] )
			|| empty( $meta['hash'] )
			|| 'eventbrite' !== $meta['origin']
			|| 'https://www.eventbrite.com/me' !== $meta['source']
		) {
			do_action( 'tribe_log', 'debug', __METHOD__, [ 'The import does not meet all the required criteria' ] );

			return;
		}

		$result = tribe( 'events-aggregator.main' )
			->api( 'import' )
			->update(
				$meta['import_id'],
				[
					'callback' => home_url( '/event-aggregator/insert/?key=' . urlencode( $meta['hash'] ) ),
					'origin'   => $meta['origin'],
				]
			);

		do_action(
			'tribe_log',
			is_wp_error( $result ) ? 'warning' : 'debug',
			__METHOD__,
			[
				'import_id' => $meta['import_id'],
				'hash'      => $meta['hash'],
				'origin'    => $meta['origin'],
			]
		);
	}
}
