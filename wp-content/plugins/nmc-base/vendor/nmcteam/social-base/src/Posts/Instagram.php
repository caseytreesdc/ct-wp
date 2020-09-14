<?php

namespace NMC_Social\Posts;

use NMC_Social\Post;

/**
 * Instagram Post
 */
class Instagram extends Post {

	/**
	 * URL
	 *
	 * @return string
	 */
	public function url()
	{
		return 'https://www.instagram.com/p/'.$this->code;
	}

    /**
     * Get Created at Field
     *
     * @return string
     */
    public function getCreatedAtField()
    {
        return $this->date;
    }

	/**
	 * Likes
	 *
	 * Returns the number of likes for a
	 * particular Instagram post.
	 *
	 * @return int
	 */
	public function likes()
	{
		if (isset($this->edge_liked_by->count)) {
			return intval($this->edge_liked_by->count);
		} else {
			0;
		}
	}
}