<?php

namespace NMC_Social;

use DateTime;
use DateTimeZone;

/**
 * Post
 *
 * Basic post class that can be used
 * for most items retrieved from various
 * APIs.
 */
class Post {

	/**
	 * __construct
	 *
	 * Takes an array or object of an item
	 * and assigns the data to class properties
	 *
	 * @param array|object $item
	 */
	public function __construct($item)
	{
		if (is_array($item) or is_object($item)) {
			foreach ($item as $key => $value) {
				$this->$key = $value;
			}
		}
	}

	/**
	 * Created
	 *
	 * Every post will need a created() function
	 * that returns a DateTime instance - which can be easily
	 * formatted in Twig.
	 *
	 * @return DateTime
	 */
	public function created()
	{
		return new DateTime($this->getCreatedAtField(), new DateTimeZone('America/New_York'));
	}

	/**
	 * Get Craeted at Field
	 *
	 * Return the field value of whatever piece
	 * of data represents the date for this item.
	 *
	 * @return string
	 */
	public function getCreatedAtField()
	{
		return null;
	}

}
