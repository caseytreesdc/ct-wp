<?php

namespace NMC_Social\Posts;

use DateTime;
use DateTimeZone;
use SimpleXMLElement;

/**
 * RSS Item
 */
class RssItem {

	/**
	 * XML Element
	 *
	 * @var SimpleXMLElement
	 */
	public $xmlElement;

	public function __construct(SimpleXMLElement $xmlElement)
	{
		$this->xmlElement = $xmlElement;

		// Set standard XML elements. If you need any non-standard
		// elements, __get should pick them out of the xml element.
		$this->guid = $xmlElement->guid;
		$this->title = $xmlElement->title;
		$this->link = $xmlElement->link;
		$this->description = $xmlElement->description;
		$this->pubDate = $xmlElement->pubDate;
	}

	/**
	 * Title Link
	 *
	 * @return string
	 */
	public function titleLink()
	{
		return '<a href="'.$this->link.'">'.$this->title.'</a>';
	}

	/**
	 * Created
	 *
	 * @return DateTime
	 */
	public function created()
	{
		return new DateTime($this->pubDate, new DateTimeZone('America/New_York'));
	}

	/**
	 * __get
	 *
	 * @param  $key
	 */
	public function __get($key)
	{
		if (isset($this->xmlElement->$key)) {
			return $this->xmlElement->$key;
		} else {
			return null;
		}
	}
}