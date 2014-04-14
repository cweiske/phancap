************************************
phancap - website screenshot service
************************************

Web service to create website screenshots.

Self-hosted and written in PHP. Caching included.


==============
URL parameters
==============
``get.php`` supports the following parameters:

Browser parameters
==================
``url``
  Website URL
``bwidth``
  Browser width (default: 1024)
``bheight``
  Browser height (default: none)

Screenshot parameters
=====================
``swidth``
  Screenshot width (default: none (no scaling))
``sheight``
  Screenshot height (default: none)
``sformat``
  Screenshot format (``png``, ``jpg``, ``pdf``, default: ``png``)
``smode``
  Screenshot mode (``screen`` (4:3) or ``page`` (full website height))
``smaxage``
  Maximum age of screenshot in seconds.
  ISO 8601 duration specifications accepted:

  - ``P1Y`` - 1 year
  - ``P2W`` - 2 weeks
  - ``P1D`` - 1 day
  - ``PT4H`` - 4 hours

  The configuration file defines a minimum age that the user cannot undercut
  (``$screenshotMinAge``), as well as a default value (``$screenshotMaxAge``).

Authentication parameters
=========================
``atimestamp``
  Time at which the request URL was generated (unix timestamp)
``atoken``
  Access token (username)
``asignature``
  Signature for the request. See the authentication section.


==============
Authentication
==============
Creating screenshots of websites is a resource intensive process.
To prevent unauthorized access to the service, phancap supports authentication
via a signature parameter similar to OAuth's ``oauth_signature``.

Phancap's configuration file may contain a ``$access`` variable:

``true``
  Everyone is allowed to access the service
``false``
  Nobody is allowed to access the service
``array``
  A list of usernames that are allowed to request screenshots, together
  with their secret keys (password)

The signature algorithm is as follows:

#. Parameters ``atimestamp`` (current unix timestamp) and
   ``atoken`` (username) have to be added to the URL parameters

#. URL parameters are normalized as described in
   `OAuth Parameters Normalization`__:

   #. Sort parameters list by name
   #. Name and value are `raw-url-encoded`__
   #. Name and value are concatenated with ``=`` as separator
   #. The resulting strings are concatenated with ``&`` as separator

#. URL parameter string is used together with the secret key
   to create a `HMAC-SHA1`__ digest

#. Digest is appended to the URL as ``asignature``

__ http://tools.ietf.org/html/rfc5849#section-3.4.1.3.2
__ http://tools.ietf.org/html/rfc5849#section-3.6
__ http://tools.ietf.org/html/rfc5849#section-3.4.2


Example
=======

.. note::

    The ``docs/`` directory contains an example PHP client implementation.

We want to create a screenshot of ``http://example.org/`` in size 400x300,
using the browser size of 1024x768::

    http://example.org/phancap/get.php?swidth=400&sheight=300&url=http%3A%2F%2Fexample.org%2F&bwidth=1024&bheight=768

Phancap's config file contains::

    $access = array(
        'user' => 'secret'
    );

Our parameters are thus:

============== =====
Name           Value
============== =====
``swidth``     ``400``
``sheight``    ``300``
``url``        ``http://example.org/``
``bwidth``     ``1024``
``bheight``    ``768``
============== =====

At first, we need to add parameters ``atimestamp`` and ``atoken``.
``atimestamp`` is the current unix timestamp.
``atoken`` is our user name: ``user``.

Now the parameter list is sorted:

============== =====
Name           Value
============== =====
``atimestamp`` ``1396353987``
``atoken``     ``user``
``bheight``    ``768``
``bwidth``     ``1024``
``sheight``    ``300``
``swidth``     ``400``
``url``        ``http://example.org/``
============== =====

The parameters are raw-url-encoded. The only value that changes is the url,
it becomes ``http%3A%2F%2Fexample.org%2F``.

Concatenating the name/value pairs leads to the following string::

    atimestamp=1396353987&atoken=user&bheight=768&bwidth=1024&sheight=300&swidth=400&url=http%3A%2F%2Fexample.org%2F

Creating the HMAC digest with sha1, the calculated string and our key
``secret`` gives us the following string::

    9a12eac5ff859f9306eaaf5a18b9a931fe10b89d

This is the signature; it gets appended the URL as ``asignature`` parameter.


============
Dependencies
============
- `cutycapt <http://cutycapt.sourceforge.net/>`_
- imagemagick's ``convert``
- ``xvfb-run``
- PEAR's ``System.php``


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
