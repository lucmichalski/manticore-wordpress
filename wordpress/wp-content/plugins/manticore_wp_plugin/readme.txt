=== Manticore Search Plugin ===
Contributors: Manticore Software, Percona
Donate link: https://manticoresearch.com/
Tags: search, sphinx, manticore
Requires at least: 2.0.2
Tested up to: 4.8
Stable tag: 3.10.5
License: GPLv2

Manticore Search Plugin allows to use Sphinx Search Server power to enable ultra-fast and feature-rich search on WordPress-based websites.

== Description ==

Manticore Search Plugin allows to use Sphinx Search Server power to enable ultra-fast and feature-rich search on WordPress-based websites. It is especially useful if your WordPress site becomes very large.

Search results are more relevant and you can search in posts, pages and comments using flexible search syntax, quickly sort the results by freshness, relevance or in both modes. This plugin comes with sidebar widgets to display the most recent searches, top and related search terms.

This plugin replaces WordPress’s built-in search functionality.

Key Features

 * Sort search results by Relevance, Freshness or in both modes
 * Related searches widget is a great SEO tool for finding related search terms
 * Top searches widget is another SEO tool which displays top search terms when no related search terms found
 * Latest searches widget is a SEO tool which is used to display latest search terms of those people are doing right now
 * Widget settings have plenty of options to control widget behavior and quality of widget content
 * Search through posts, pages and comments content
 * Search Management Tools is a set of tools for managing search terms
 * Search Statistics Tool is an analytic tool which can help you analyze search trends of your blog

Support

This plugin is developed by [Manticore Software](https://manticoresearch.com/ "Manticore Software"). If you need commercial support, or if you’d like Manticore Search Plugin
customized for your needs, we can help. Visit [plugin website](http://www.ivinco.com/software/wordpress-sphinx-search/ "plugin website") for the latest news.
See release notes, report bugs and feature wishes on Launchpad: https://bugs.launchpad.net/wp-sphinx-plugin

E-mail:
contact@manticoresearch.com

Websites:

[Manticore Search Plugin website](https://manticoresearch.com/ "Manticore Search Plugin")

[Manticore Search Plugin tutorial](http://www.ivinco.com/software/wordpress-sphinx-search-tutorial/ "Manticore Search Tutorial")

== Installation ==

= Requirements =

    * WordPress 2.0.2 or higher
    * Sphinx Search 2.1.9 or higher
    * Writable WordPress upload directory for Sphinx configuration files, logs and indexes

= Installation guide =
[Online step-by-step installation guide](http://www.ivinco.com/software/wordpress-sphinx-search-tutorial/#installation "Step-by-step installation guide")

= Install the plugin =

   1. Unpack the plugin archive to wp-content/plugins folder of your WordPress installation
   2. Activate Sphinx Search plugin via WordPress Settings
   3. Make sure WordPress upload directory is writable by web server (by default WordPress is configured to use wp-content/uploads)
   4. Open Sphinx Search settings page and follow Wizard steps to setup Sphinx Search Server first time

= Setup scheduled jobs to re-index your website data periodically =
To setup periodical re-indexing, you should run Wizard to create special schedule files.
The default location of these files is: /path/to/wp-content/uploads/sphinx/cron/.
When wizard finishes, edit your Crontab file.
Use “crontab -e” command in Linux terminal and add the following lines to your crontab:

= Setup templates and widgets =
Extended search form on search results page
`<?php if (function_exists('ss_search_bar'))
    echo ss_search_bar();/*put it in search page*/?>`

To find out if the current post is comment
`<?php if (function_exists('ss_isComment') )
    if (ss_isComment()) echo 'It is comment'; else echo '';?>`

Extended search form at the sidebar
Use "Sphinx Search sidebar" widget or add it as template tag:
`<?php if (function_exists('ss_search_bar'))
    echo ss_search_bar(true); /*put it in sidebar*/?>`

Related/Top searches at the sidebar
Use "Manticore Related/Top Searches" widget or add it as template tag:
`<?php if (function_exists('ss_top_searches')) ss_top_searches(); ?>`

Top searches with pagination
Use "ss_top_searches_pager($max_per_page=10, $show_all=false)" template tag to enable pagination for top search terms:
`<?php if (function_exists('ss_top_searches_pager')) ss_top_searches_pager(); ?>`
Parameters:
 * $max_per_page - limit how many search terms to display per page, by default 10
 * $show_all - If set to True, then it will show all of the pages instead of a
short list of the pages near the current page. By default, the 'show_all' is set to false

Latest searches at the sidebar
Use "Sphinx Latest Searches" widget or add it as template tag:
`<?php if (function_exists('ss_latest_searches')) ss_latest_searches(); ?>`

= Upgrade the plugin =

   1. Unpack the plugin archive to wp-content/plugins folder of your WordPress installation

== Frequently Asked Questions ==

Q: What is Sphinx Search Server?

A: Sphinx is a full-text search engine which provides fast and relevant full-text search functionality. Read more on Sphinx website http://sphinxsearch.com

Q: How to install Sphinx Search manually?

A: To manually install Sphinx use the official Sphinx Search documentation.

Q: How to update the search index?

A: The best option to update search index is to setup cron job task for it.
Also you may manually update search indexes through WordPress Sphinx Search administrative interface.

Q: I have just activated the plugin, however when I try to run the Plugin's Wizard it does not do anything. What is wrong?

A: This might be jQuery version collision. Our plugin uses jQuery v1.4 and supposed to work with WordPress up to 3.8 version
which uses jQuery v1.x (i.e. 1.10 for WP3.8). Check if your installation has custom plugin or WP is modified to use
jQuery v2.x. If so then it will impossible to use our plugin's Wizard.

Q: I have just installed and run the wizard, however I have the following error message. What to do?

Can not start searchd, try to start it manually.

A: If you use version 2.1 or higher you can see the exact command below message “Can not start searchd, try to start it manually.”
 which you need to run manually through terminal on your server.
If you have ‘Permissions problem’ try to run the command as super user.

Q: How to run indexer manually
A: Open terminal and run following command: `/path/to/indexer -c /path/to/sphinx.conf --rotate --all`

Q: When I run searchd or indexer I got ERROR: invalid section type 'X-Powered-By' in ../sphinx.conf line 1 col 1.

A: You are using CGI version of php, by default it shows a http header like "X-Powered-By: PHP/4.3.6"
To prevent this, PHP needs to be invoked with the '-q' option for 'quiet'. Open sphinx.conf in editor and change first line to:
`#!/usr/bin/php -q`


Q: I got WARNING: index 'wp_main': preload: failed to open /path/to/indexes/wp_main.sph No such file or directory; NOT SERVING

A: That means you have no indexes to serve. You need to build them. You may do it via wp-admin or manually:
On wp-admin>Settings>Sphinx Search page click "Re-index WordPress index"
Or use run this command manually in terminal:
`/path/to/indexer -c /path/to/etc/sphinx.conf --all --rotate`

Q: Sphinx installs fine, but when I go to search for something on the blog I get no results.

A: Check Sphinx version, Sphinx version should be 0.9.9 or higher.

Q: How do I modify the plugin php script to specify the path where I have Sphinx installed?

A: Run Sphinx Configuration wizard from WP Admin panel.
There are two important steps:
1. Install or use existing Sphinx binaries
There you can specify the path to your own indexer and searchd
2. Setup path to Sphinx indexes
There you can specify where to store index files and sphinx.conf file. This path should be writeable by web server.

Q: Cannot activate plugin. If I try to activate the plugin I get the following PHP error:
Fatal error: Cannot redeclare class SphinxClient in /home/wordpress/wp-content/plugins/wordpress-sphinx-plugin/php/sphinxapi.php

A: Check that you haven't:
1. any other plugins which loaded Sphinx Search API library.
2. Sphinx Search PECL extension installed

Q: I’ve got an error “Indexer: configuration files not found.” on clicking “Run Indexing & Contunue” (“Sphinx data indexing” step of Wizard).

A: Check that the user which is running your web server (it's usually apache, www-data or smth like this)
can run indexer/searchd and can read/write into sphinx.conf. Then run Sphinx Configuration wizard from WP Admin panel again.

Q: How to set the maximum number of search results above 100.000?

A: 1)Go to Sphinx Search plugin directory and open rep/sphinx.conf in text editor.
2)Find max_matches parameter in searchd section at the bottom of the file
3)Set new value i.e. max_matches = 1000000
4)Open Sphinx Search control panel in WP Admin
5)Click on "Run Sphinx configuration Wizard" and skip all steps in the Wizard, it will rebuilds sphinx.conf file at the last step, click Finish.
6)Restart Sphinx Search (click on "Stop Sphinx daemon" and then "Start Sphinx daemon")
7)Open tab "Search settings" and set the same max_matches value in the field "Maximum number of search results".



== Screenshots ==

1. You can search by Relevance, Freshness or in both modes
2. Related searches widget is a great SEO tool for finding related search terms
3. Search Management Tools is a set of tools for managing search terms
4. Search Statistics Tool is an analytic tool which can help you analyze search trends of your blog
5. Widget settings have plenty of options to control widget behavior and quality of widget content

== Arbitrary section ==

= Semi live update - "main+delta" scheme =

If your WordPress installation's table prefix is not "wp_", substitute
the correct value.

= Top and last search terms =
In order to be able to store search statistics the plugin will run
the following SQL query during the activation process:
`
# in MySQL
CREATE TABLE wp_sph_stats (
    id int(11) unsigned NOT NULL auto_increment,
    keywords varchar(255) NOT NULL default '',
    date_added datetime NOT NULL default '0000-00-00 00:00:00',
    keywords_full varchar(255) NOT NULL default '',
        status tinyint(1) NOT NULL DEFAULT '0',
    PRIMARY KEY  (id),
    KEY keywords (keywords)
);
`
If your WordPress installation's table prefix is not "wp_", substitute it with
the correct value.

= Start Sphinx Search at boot =
#How to automatically start Sphinx Search daemon at boot:
*   In Debian based systems i.e. Ubuntu:
`% update-rc.d "/path/to/bin/searchd --config /path/to/etc/sphinx.conf" defaults`
*   In Redhat based systems i.e. Fedora:
`% chkconfig --add "/path/to/bin/searchd --config /path/to/etc/sphinx.conf"`

== Upgrade Notice ==

= 3.0 =
 This release comes with big improvements in performance of Top/Related and Latest widgets.
 We replaced MySQL FullText engine with Sphinx Search engine in all components.
 We added new search mode "Freshness & Relevance" which works perfect for blogs and news sites.

== Changelog ==

= 3.10.4 = 
* Properly handle DB_HOST when it contains a non-default unix socket

= 3.10.1 = 
* Changed plugin release version for WordPress 4.7.1, CSS fix 

= 3.9.8 =
 * Changed plugin release version for WordPress 4.2
 
= 3.9.7 =
 * Plugin release for WordPress 4.2

= 3.9.6 =
 * Plugin code was revised, fixed and tested to use with WordPress 4.2
 
= 3.9.5 =
 * Plugin code was revised and tested with WordPress 4.1

= 3.9.4 =
 * Plugin code was revised and tested with WordPress 4.0 Benny.

= 3.9.1 =
 * Updated Sphinx Search engine binaries to version 2.1.9.
 
= 3.9 =
 * Checked compatibility with WP v 3.9. Updated common styles.

= 3.8.3 =
 * FAQ page is updated. Added description of the problem with Plugin's Wizard.

= 3.8.2 =
 * Plugin will use static SSE configuration file now instead of dynamic one.

= 3.8.1 =
 * Updated installer's auto-detection mechanism which will allow user admin to choose pre-installed Sphinx binaries even on Windows platform.

= 3.8 =
 * Updated styles to fit new default theme

= 3.7 =
 * Added support of WP version up to 3.7.1
 * New feature: Tags search. added missed files.
 * New feature: Added an opportunity to search within post tags.
 * Fixed duplicates of statistics when seo URLs are used.
 * Fixed bug with single quote character.
 * Updates to layout of sphinx installation wizard.
 * Small fixes to verification logic of sphinx dirs security.
 * There is a warning of insecure files has been added to plugin configuration section
 * Fixed bug with unescaped html in snippets

= 3.3.4 =
 * fix of the bug related with searching for words that are not in the index

= 3.3.3 =
 * few miscellaneous fixes in the plugin admin zone
 * insert into sph_stat table improvement

= 3.3.2 =
 * Unhooked unnecessary filters
 * Added checking Sphinx connection on remote or local server to determine is Sphinx running

= 3.3.1 =
 * Fixed bug in search term escaping
 * Fixed bug in redirect for friendly URLs

= 3.3.0 =
 * Added friendly URLs support
 * Added ability to enable/disable friendly urls in Top/Related and Latest search terms widgets
 * Added ability to set maximum number of search results
 * Added tag for listing top search terms on a page
 * Fixed several bugs

= 3.0.2 =
 * Added new template tag: top search terms with pagination

= 3.0.1 =
 * Fixed bug in ss_isComment tag
 * Fixed search term highlighting bug

= 3.0 =
 * Added search management tool
 * Added search statistics/analytic report
 * Added new search mode "Freshness & Relevance"
 * Reworked Admin user interface, made it more simple and clear.
 * Added ability to add custom search terms to the top of the Top/Related widget
 * New option "Show only approved search terms" in Top/Related and Latest widgets
 * New option "Show top Searches for last: day, week, months..." in Top/Related widget
 * Added option to run php in quiet mode in sphinx.conf to prevent displaying HTTP headers
 * Replaced MySQL FullText engine with Sphinx Search in Top/Related and Latest widgets
 * Built search management tool and statistics tool over Sphinx Search
 * Added custom css styles file (templates/style.css) for search forms
 * Ivinco icon looking more smoothly with transparent background


= 2.1 =
 * Added more settings to Top/Related widget.
 * A few bug fixes and minor improvements

= 2.0 =
*   Added configuration wizard: you can automatically install or reinstall Sphinx via WordPress wp-admin panel
*   Changed default Sphinx installation directory: now Sphinx is installed to WordPress upload directory
*   Using shebang syntax for Sphinx configuration file - it allows to hide the connection parameters from the public access
*   UI fixes for WordPress wp-admin panel
*   Improved error handling: system output is now hidden and human readable error messages were added
*   Added a new sidebar widgets for displaying top/related and latest search terms and a widget for the extended search form
*   Added search term highlighting for search results (in WordPress' the_excerpt tag)
*   Added automatic generation of cron files

= 1.0 =

*   Added Search powered by Sphinx Search engine
*   Added sort search resutls by Relevance or Freshness
*   Added search by posts, by comments and by pages.
*   Added exclude posts, comments or pages from search results.
*   Added display comments at search results page.
*   Added search non-password protected pages only
*   Added search only approved comments
*   Added "Match Any" search ability - if no one results was found, then it try to search in "Match Any" mode.
*   Added support for tag title of web page - it changed due to entered search keywords
*   Added log of all search results, except empty results
*   Added tag to display Top-n search keywords
*   Added tag to display Latest-n search keywords
*   Added relevant keywords support to the Top-n search keywords bar.
*   Added support to Disable/Enable search by: comments, posts, pages
*   Added support of keywords wrapper settings:
   *   Add tag Before and After search keyword in body/title
   *   Separator of result snippets
   *   Snippet max length
   *   Maximum number of words around keyword in snippet
   *   Prefix before Posts, Comments and Pages title
   *   List of phrases to cut from search results
*   Added configuration for Sphinx index prefix
*   Added configuration for Host
*   Added configuration for Port
*   Added configuration for Configuration file
*   Added configuration for Searchd file
*   Added configuration for Indexer file
*   Added support to reindex all content manually
*   Added support to stop/start search daemon
*   Added support to install Sphinx Search through web interface
