<?php

namespace NMC_Social\Posts;

use NMC_Social\Post;

class Tweet extends Post {

    /**
     * Formatted
     *
     * Turn the following into links:
     *
     * - links
     * - hashtags
     * - @ replies
     *
     * @return string
     */
    public function formatted()
    {
        $tweet = $this->text;

        //Convert urls to <a> links
        $tweet = preg_replace("/([\w]+\:\/\/[\w\-?&;#~=\.\/\@]+[\w\/])/", "<a target=\"_blank\" href=\"$1\">$1</a>", $tweet);

        //Convert hashtags to twitter searches in <a> links
        $tweet = preg_replace("/#([A-Za-z0-9\/\.]*)/", "<a target=\"_new\" href=\"http://twitter.com/search?q=$1\">#$1</a>", $tweet);
        //Convert attags to twitter profiles in &lt;a&gt; links
        $tweet = preg_replace("/@([A-Za-z0-9\/\.]*)/", "<a href=\"http://www.twitter.com/$1\">@$1</a>", $tweet);

        return $tweet;
    }

    /**
     * Timestamp
     *
     * UNIX timestamp for post date
     *
     * @return  int unix time stamp
     */
    public function timestamp()
    {
        return strtotime($this->created_at);
    }

    /**
     * Get Created at Field
     *
     * @return DateTime
     */
    public function getCreatedAtField()
    {
        return $this->created_at;
    }

    /**
     * Time Ago
     *
     * Simple time ago function that
     * either shows the number of days ago,
     * or "Today".
     *
     * Partially lifed from:
     * https://stackoverflow.com/questions/1416697/converting-timestamp-to-time-ago-in-php-e-g-1-day-ago-2-days-ago
     * 
     * @return string
     */
    public function timeAgo()
    {
        $now = new \DateTime;
        $ago = new \DateTime($this->created_at);

        $diff = $now->diff($ago);

        // Less than 1 day? Just
        // say "today"
        if ($diff->d < 1) {
            return 'Today';
        }

        $diff->w = floor($diff->d / 7);
        $diff->d -= $diff->w * 7;

        $string = array(
            'y' => 'year',
            'm' => 'month',
            'w' => 'week',
            'd' => 'day'
        );
        foreach ($string as $k => &$v) {
            if ($diff->$k) {
                $v = $diff->$k . ' ' . $v . ($diff->$k > 1 ? 's' : '');
            } else {
                unset($string[$k]);
            }
        }

        return $string ? implode(', ', $string) . ' ago' : 'Today';
    }

}