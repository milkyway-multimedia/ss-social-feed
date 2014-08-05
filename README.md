Social Feed
======
**Social Feed** allows the display of a social feed (duh) on your website.

I have not added a Page template for the SocialFeed page type, since I prefer creating my own. However, all the includes are there.

My suggestion is to load the feed with AJAX, so it does not slow the page down. Another solution could be to simply ping the page once every 6 hours (or whatever cache time you use), so that it will store it in cache.

## Install
Add the following to your composer.json file

```

    "require"          : {
		"milkyway-multimedia/silverstripe-social-feed": "dev-master"
	}

```

### Social Profile Types
Currently only the following are supported:

- Facebook Pages/Profiles
- Twitter Account
- Google Plus Page
- Internal Page (will display children of said page)

I am hoping to add Instagram and maybe Pinterest when I have time... But you can have as many of the above accounts as you want (if you don't mind the lag...)

### Features
- Display all your social activity of one page
- Use AddThis to encourage sharing
- You can add follow buttons and like post buttons, as well as use hash tag retweets with Twitter.
- JS is deferred for faster load times! No more social plugins annoyingly slowing down the website!!

## License
* MIT

## Version
* Version 0.1 - Alpha

## Contact
#### Milkyway Multimedia
* Homepage: http://milkywaymultimedia.com.au
* E-mail: mell@milkywaymultimedia.com.au
* Twitter: [@mwmdesign](https://twitter.com/mwmdesign "mwmdesign on twitter")