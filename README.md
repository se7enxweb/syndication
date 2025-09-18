# Syndication Extension

syndication is an extension that allows the integration of a stable one way or two way bi directional syndication of content tree content between instsallation which are separate yet with this solution now connected in a powerful way.

Version: 1.1.0

- GitHub: [https://github.com/se7enxweb/syndication](https://github.com/se7enxweb/syndication)

- Composer: [https://packagist.org/packages/se7enxweb/syndication](https://packagist.org/packages/se7enxweb/syndication)

## Features

Tested and Working Features

- 1 way syndication ( example: shared authentication between separate database installation / client sites)

- 2 way syndication ( example: forum, larger portal networks, social platforms, enterprise e-commerce networks )

## About 7x

7x in 2025 was the first to release the eZ Systems extension syndication for public download and then upgrade it, maintain it and document it for long term wide spread usage to resume. 

7x is a web design, development, support and hosting company from North America operating for over 24 years. Formerly known as Brookins Consulting a leader in both the eZ Publish Partner Community and eZ Publish Open Source Project. 7x supports cutting edge development and support for the Exponential Community and eZ Publish Ecosystem.

From: [https://se7enx.com/](https://se7enx.com/)

## Installation

See [INSTALL.md](INSTALL.md)

## Usage

- Install the extension on your main site. Ensure the extension sql is installed into your database.

- Activate the extension via settings.

- Configure 1 way syndication (Export creation) to provide content tree node subtree selection of content tree nodes as feed content to export.

- Configure your servers crontab to launch the syndication cronjobs for export_feed cronjob part regularly say every 3 min since it takes much load once established and running. 

- Install the extension on the second / secondary site which will import the previously exported content.

- Configure 1 way syndication (Import creation) to provide for the content tree node subtree selection of your secondary site to import the feed's content into your secondary site for regular use, management and display.

- Configure your servers crontab to launch the syndication cronjobs for import_feed cronjob part regularly say every 3 min since it takes much load once established and running. 

Note: We recommend first launching the cronjobs by hand for your first sync attempt. this allows you to review the initial import of content and after imports will be faster and not need regular watching as the system is stable and reliable.

Enjoy the freedom you have to build large scale networks using soap, syndication extension, and Exponential 6.x Enterprise Open Source CMS.

Cheers!

# License

Syndication extension to Exponential is licensed GNU GPLv2 (or any later version)

This file may be distributed and/or modified under the terms of the "GNU
General Public License" version 2 as published by the Free Software Foundation

This file is provided AS IS with NO WARRANTY OF ANY KIND, INCLUDING THE
WARRANTY OF DESIGN, MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE.

The "GNU General Public License" (GPL) is available at
[http://www.gnu.org/copyleft/gpl.html](http://www.gnu.org/copyleft/gpl.html).
