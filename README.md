dash-phalcon
============

[Phalcon](http://phalconphp.com) PHP documentation for Dash

This docset is to be used with [Dash for Mac](http://kapeli.com/dash).

Based on Phalcon version **1.2.6**

**Script generation phalconParser.php**

- Download the HTML documentation on [phalcon website](http://media.readthedocs.org/htmlzip/phalcon-php-framework-documentation/latest/phalcon-php-framework-documentation.zip)
- Follow the [instructions](http://kapeli.com/docsets) to generate a Dash docset
- In phalconParser.php, adjust the followong constants :
	- PHALCON_API_FOLDER: full path where are stored the HTML files (phalcon-php-framework-documentation-latest/api)
	- HTML_DESTINATION_FOLDER: full path of the target HTML files (phalconphp.docset/Contents/Resources/Documents/api)
	- SQLITE_FILE: full path of the Dash SQLite file
