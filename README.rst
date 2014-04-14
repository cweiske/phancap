************************************
phancap - website screenshot service
************************************

Web service (API) to create website screenshots.

Self-hosted and written in PHP. Caching included.


.. contents::

===============
Getting started
===============

Basic setup
===========
#. Download the ``.phar`` file and put it onto your web server
#. Open the phar file in your browser
#. Click the "setup check" link
#. Fix all errors that are reported
#. Run ``phancap.phar/get.php?url=cweiske.de`` and see the screenshot


Advanced setup
==============
With the basic setup, everyone may use your server to create website
screenshots.
You may want to change that or simply change some default settings.

#. Create a config file ``phancap.phar.config.php``
#. Edit it; see the configuration_ options.


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
  Signature for the request. See the authentication_ section.


=============
Configuration
=============
phancap looks at several places for its configuration file:

#. ``phancap.phar.config.php`` in the same directory as your
   ``phancap.phar`` file.

#. ``/etc/phancap.php``


Configuration variables
=======================
``$cacheDir``
  Full file system path to image cache directory
``$cacheDirUrl``
  Full URL to cache directory
``$access``
  Credentials for access control

  ``true`` to allow access to anyone, ``false`` to disable it completely.
  ``array`` of username - secret key combinations otherwise.
``$disableSetup``
  Disable ``setup.php`` which will leak file system paths
``$redirect``
  Redirect to static image urls after generating them
``$timestampmaxAge``
  How long a signature timestamp is considered valid. 2 days default.
``$screenshotMaxAge``
  Cache time of downloaded screenshots.

  When the file is as older than this, it gets re-created.
``$screenshotMinAge``
  Minimum age of a screeshot. 1 hour default.
 
  A user cannot set the max age parameter below it.



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
  with their secret keys (password)::

    $access = array(
       'user1' => 'secret1',
       'user2' => 'secret2',
    )

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

This is the signature; it gets appended to the URL as ``asignature`` parameter.


============
Dependencies
============
- `cutycapt <http://cutycapt.sourceforge.net/>`_
- imagemagick's ``convert``
- ``xvfb-run``
- PEAR's ``System.php``


=======
License
=======
``phancap`` is licensed under the `AGPL v3`__ or later.

__ http://www.gnu.org/licenses/agpl.html


========
Homepage
========
Web site
   http://cweiske.de/phancap.htm

Source code
   http://git.cweiske.de/phancap.git

   Mirror: https://github.com/cweiske/phancap


======
Author
======
Written by Christian Weiske, cweiske@cweiske.de
