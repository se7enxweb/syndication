#!/usr/bin/env php
<?php
/**
 * Fetch the syndication imports and import the waiting items (cronjob part import_feed, by hand)
 *
 * Usage:
 *   php extension/syndication/bin/php/import.php [options]   (or ./console ext:syndication:import)
 *   --help shows the options.
 *
 * @alias syn-import
 * @copyright Copyright (C) 1998 - 2026 7x and the Exponential Foundation. All rights reserved.
 * @license GNU General Public License v2.0 (or any later version)
 * @package syndication
 */

require_once dirname( __FILE__ ) . '/../../../../autoload.php';

// The code is in extension/syndication/classes/runnable/commands/php_import.php (#207); this file is the entry point.
\Exponential\Command\Extension\Syndication\Import::main( __FILE__ );
