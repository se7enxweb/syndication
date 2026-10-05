<?php
// @description Fetch the item lists of the syndication imports and import the waiting items
/**
 * Cronjob part import_feed of the syndication extension.
 *
 * @copyright Copyright (C) 1998 - 2026 7x and the Exponential Foundation. All rights reserved.
 * @license GNU General Public License v2.0 (or any later version)
 * @package syndication
 */

// The code is in extension/syndication/classes/runnable/cronjobs/php_import_feed.php (#207); this file is the entry point.
return \Exponential\Cronjob\Extension\Syndication\ImportFeed::main( __FILE__, get_defined_vars() );
