<?php

namespace NMC_Social\Posts;

use NMC_Social\Post;

class EventbriteEvent extends Post {

	public function title()
	{
		if (isset($this->name->text)) {
			return $this->name->text;
		} else {
			return null;
		}
	}

	public function startDate()
	{
		return new \DateTime($this->start->local, $this->getTimezone('start'));
	}

	public function endDate()
	{
		return new \DateTime($this->end->local, $this->getTimezone('end'));
	}

	public function getTimezone($startEnd)
	{
		if (isset($this->$startEnd->timezone)) {
			$timezoneString = $this->$startEnd->timezone;
		} else {
			$timezoneString = 'America/New_York';
		}

		return new \DateTimeZone($timezoneString);
	}

}