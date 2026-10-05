#!/usr/bin/env php
<?php
/**
 * Write the export cache of the syndication feeds (cronjob part export_feed, by hand)
 *
 * Usage:
 *   php extension/syndication/bin/php/export.php [options]   (or ./console ext:syndication:export)
 *   --help shows the options.
 *
 * @alias syn-export
 * @copyright Copyright (C) 1998 - 2026 7x and the Exponential Foundation. All rights reserved.
 * @license GNU General Public License v2.0 (or any later version)
 * @package syndication
 */

require_once dirname( __FILE__ ) . '/../../../../autoload.php';

// The code is in extension/syndication/classes/runnable/commands/php_export.php (#207); this file is the entry point.
\Exponential\Command\Extension\Syndication\Export::main( __FILE__ );
