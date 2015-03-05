(function ($) {
    $.entwine('ss', function ($) {
        $('.cms-container').entwine({
            onaftersubmitform: function(event, data) {
                this.redirectOnSocialFeedOauthRequest(data.xhr);
            },
            onafterstatechange: function(event, data) {
                this.redirectOnSocialFeedOauthRequest(data.xhr);
            },
            redirectOnSocialFeedOauthRequest: function(xhr) {
                if(xhr && xhr.getResponseHeader('X-SocialFeed-RedirectForOauth')) {
                    window.location = xhr.getResponseHeader('X-SocialFeed-RedirectForOauth');
                }
            }
        });
    });
}(jQuery));