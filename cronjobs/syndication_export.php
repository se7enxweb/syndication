<?php
// @description Update the caches of the syndication export feeds, feed by feed
/**
 * Cronjob part export_feed of the syndication extension.
 *
 * @copyright Copyright (C) 1998 - 2026 7x and the Exponential Foundation. All rights reserved.
 * @license GNU General Public License v2.0 (or any later version)
 * @package syndication
 */

// The code is in extension/syndication/classes/runnable/cronjobs/php_export_feed.php (#207); this file is the entry point.
return \Exponential\Cronjob\Extension\Syndication\ExportFeed::main( __FILE__, get_defined_vars() );
