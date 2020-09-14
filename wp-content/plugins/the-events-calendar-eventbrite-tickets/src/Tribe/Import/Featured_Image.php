<?php

namespace Tribe\Events\Eventbrite\Import;

use InvalidArgumentException;
use Tribe__Process__Handler;
use WP_Query;

/**
 * Class Featured_Image Download an image on the background from EA and use it as featured image for an event.
 *
 * @since   TBD
 *
 * @package Tribe\Events\Eventbrite\Import
 */
class Featured_Image extends Tribe__Process__Handler {

	/**
	 * A string representing the ID EB image.
	 *
	 * @var $image_id string A string representing the ID EB image.
	 */
	private $image_id;

	/**
	 * ID of the event (post ID on WordPress).
	 *
	 * @var $event_id int ID of the event (post ID on WordPress).
	 */
	private $event_id;

	/**
	 * {@inheritdoc}
	 */
	public static function action() {
		return 'eventbrite_image_downloader';
	}

	/**
	 * Associate an event ID with this action.
	 *
	 * @param $event_id int The ID where the image is going to be attached.
	 *
	 * @return $this
	 */
	public function set_event_id( $event_id ) {
		$this->event_id = $event_id;

		return $this;
	}

	/**
	 * Set the Hash ID of the image we are trying to locate.
	 *
	 * @since TBD
	 *
	 * @param string $image_id Hash ID of the image we are looking for.
	 *
	 * @return $this
	 */
	public function set_image_id( $image_id ) {
		$this->image_id = $image_id;

		return $this;
	}

	/**
	 * Dispatch of the action into the Queue.
	 *
	 * @since TBD
	 * @throws InvalidArgumentException
	 *
	 * @return mixed Result of the dispatch call.
	 */
	public function dispatch() {
		if ( ! isset( $this->image_id, $this->event_id ) ) {
			do_action(
				'tribe_log',
				'error',
				$this->identifier,
				[
					'message' => 'Requirements were not met for process this image',
				]
			);

			throw new InvalidArgumentException( 'Event ID and Image ID should be set before trying to dispatch.' );
		}

		$data = [
			'image_id' => $this->image_id,
			'post_id'  => $this->event_id,
		];

		$this->data( $data );

		do_action( 'tribe_log', 'debug', $this->identifier, $data );

		return parent::dispatch();
	}

	/**
	 * Process the action ASAP.
	 *
	 * @since TBD
	 */
	protected function handle( array $data_source = null ) {
		$this->sync_handle( $data_source );
	}

	/**
	 * Process the download of the image from EA, attempt to locate the image first on the media library if a copy
	 * of the same ID image is present if any uses that instead of download one from the EA server.
	 *
	 * @since TBD
	 *
	 * @param array|null $data_source
	 *
	 * @return bool|int|mixed|null
	 */
	public function sync_handle( array $data_source = null ) {
		$data_source = isset( $data_source ) ? $data_source : $_POST;

		if ( empty( $data_source['post_id'] ) || empty( $data_source['image_id'] ) ) {
			return false;
		}

		$licenses    = [];
		$ebt_license = tribe( 'eventbrite.pue' )->pue_instance->get_key();
		if ( ! empty( $ebt_license ) ) {
			$licenses['tribe-eventbrite'] = $ebt_license;
		}

		$image_url   = tribe( 'events-aggregator.service' )->build_url(
			"image/{$data_source['image_id']}",
			[
				'licenses' => $licenses,
				'origin'   => 'eventbrite',
			]
		);

		$attachment_id = $this->find_locally( $data_source['image_id'] );

		if ( $attachment_id ) {
			// Make sure the meta key is updated if found locally.
			update_post_meta( $attachment_id, '_tribe_importer_original_url', $image_url );
		} else {
			$attachment_id = tribe_upload_image( $image_url );
		}

		if ( ! $attachment_id ) {
			do_action(
				'tribe_log',
				'debug',
				__METHOD__,
				[
					'message' => 'Unable to find the image on the media library or to download the image.',
					'data'    => $data_source,
				]
			);

			return false;
		}

		$this->attach( $attachment_id, $data_source['post_id'] );

		return $attachment_id;
	}

	/**
	 * Find the attachment on the local attachments as maybe the image has been already downloaded, there's no need
	 * for an additional request in that case just use the ID of the previous imported image instead, if no image is
	 * found `false` is returned instead.
	 *
	 * @since TBD
	 *
	 * @param string $image_id The ID of the image we are looking for, usually a hash string.
	 *
	 * @return bool|int An ID greater than 0 is returned if the image is found false otherwise.
	 */
	private function find_locally( $image_id ) {
		$query = new WP_Query(
			[
				'post_type'              => 'attachment',
				'post_status'            => 'any',
				'posts_per_page'         => 1,
				'no_found_rows'          => true,
				'update_post_term_cache' => false,
				'update_post_meta_cache' => false,
				'fields'                 => 'ids',
				'meta_query'             => [
					[
						'key'   => 'tribe_aggregator_image_id',
						'value' => $image_id,
					],
				],
			]
		);

		$posts = $query->get_posts();

		return is_array( $posts ) ? reset( $posts ) : false;
	}

	/**
	 * Link attachment and event, by setting the attachment as the featured image of the event and attaching
	 * the attachment to the event.
	 *
	 * @since TBD
	 *
	 * @param $attachment_id  int ID of the attachment.
	 * @param $event_id       int ID of the event.
	 *
	 * @return bool|int If the operation to set the thumbnail was successful or not.
	 */
	private function attach( $attachment_id, $event_id ) {
		set_post_thumbnail( $event_id, $attachment_id );

		return wp_update_post(
			[
				'ID'          => $attachment_id,
				'post_parent' => $event_id,
			]
		);
	}
}
