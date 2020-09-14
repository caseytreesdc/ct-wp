<?php

namespace NMC_Social\Posts;

use NMC_Social\Post;

/**
 * Facebook Post
 */
class FacebookPost extends Post {

    /**
     * Get Created at Field
     *
     * @return string
     */
    public function getCreatedAtField()
    {
        return $this->created_time;
    }

}