// Twlinkify
// an absurdly simple jQuery plugin to linkify the text of tweets

;(function($) {

   function addTwitterLinks(t) {
        var e = /(\b(https?|ftp|file):\/\/[-A-Z0-9+&@#\/%?=~_|!:,.;]*[-A-Z0-9+&@#\/%=~_|])/gi;
        t = t.replace(e, "<a href='$1' target='_blank'>$1</a>");
        e = /(^|\s)#(\w+)/g;
        t = t.replace(e, "$1<a href='https://twitter.com/search?q=%23$2&src=hash' target='_blank'>#$2</a>");
        e = /(^|\s)@(\w+)/g;
        t = t.replace(e, "$1<a href='http://www.twitter.com/$2' target='_blank'>@$2</a>");
        return t;
    }

    $.fn.twlinkify = function() {
        return this.each(function() {
            var $el = $(this);
            $el.html(addTwitterLinks($el.text()));
        });
    };

})(jQuery);