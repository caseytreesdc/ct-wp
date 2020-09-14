<?php

namespace Tribe\Events\Eventbrite;

use Tribe__Events__Tickets__Eventbrite__Main;

/**
 * Class Admin
 *
 * @since   TBD
 * @package Tribe\Events\Eventbrite
 */
class Admin {
	/**
	 * Before the template from TEC is loaded load a template from EBT instead.
	 *
	 * @since TBD
	 *
	 * @param string $file Complete path to include the PHP File.
	 * @param array  $name Template name.
	 * @param array  $data The Data that will be used on this template.
	 *
	 * @return string Complete path of the file being included.
	 */
	public function overwrite_templates( $file, $name, array $data ) {
		if ( empty( $name ) || ! is_array( $name ) ) {
			return $file;
		}

		// Join all the names using a "_" as separator of multiple names.
		$name = implode( '_', $name );

		// Not the file we are looking for.
		if ( 'origins_eventbrite' !== $name ) {
			return $file;
		}

		$path = [
			Tribe__Events__Tickets__Eventbrite__Main::instance()->plugin_path,
			'src',
			'admin-views',
			'aggregator',
			'origins',
			'eventbrite.php',
		];

		return implode( DIRECTORY_SEPARATOR, $path );
	}
}
