=======================
Technical brainstorming
=======================

Tools to make website screenshots
=================================
- `cutycapt <http://cutycapt.sourceforge.net/>`_
- `khtml2png <http://khtml2png.sourceforge.net/>`_ (outdated)
- `phantomjs <http://phantomjs.org/>`_
- `python-webkit2png <https://github.com/AdamN/python-webkit2png/>`_
- `wkhtmltopdf <http://code.google.com/p/wkhtmltopdf/>`_
- wkhtmltoimage


Possible parameters
===================

Page request parameters
-----------------------
- url
- bwidth (browser width / resolution)
- bheight (browser height / resolution)
- delay (capture X seconds after page loaded)
- useragent (user agent header string)
- accepted languages (Accept-Language header)
- cookie (set cookie data)
- referer (custom referer header)
- post data (send POST data as if filled out a form)

Screenshot configuration
------------------------
- width (of thumbnail)
- height (of thumbnail)
- output format (jpg, png, pdf)
- mode: screen or page (full page height or screen size only)
  - page aka fullpage
- quality (jpeg image quality)

Misc
----
- callback URL (to notify when screenshot is ready)
- sync/async (wait for response or just get a 202 accepted)
- cache (to force a fresh screenshot with cache=0,
  otherwise seconds the cache may be old)
- api key
- token (md5 hash of query string)

API parameter sources
---------------------
- http://api1.thumbalizr.com/
- http://url2png.com/docs/
- http://webthumb.bluga.net/apidoc
- http://www.page2images.com/Create-website-screenshot-online-API
- http://browshot.com/api/documentation


Other website screenshot services
=================================
- http://browsershots.org/
- http://browshot.com/
- http://ctrlq.org/screenshots/
- http://grabz.it/
- http://url2png.com/
- http://usersnap.com/
- http://websnapr.com/
- http://webthumb.bluga.net/
- http://www.page2images.com/
- http://www.shrinktheweb.com/
- http://www.thumbalizr.com/
- http://www.url2picture.com/


Other website screenshot software
=================================
- https://github.com/microweber/screen
