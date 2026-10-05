#!/usr/bin/env php
<?php
/**
 * Show the state of the syndication feeds and imports
 *
 * Usage:
 *   php extension/syndication/bin/php/status.php [options]   (or ./console ext:syndication:status)
 *   --help shows the options.
 *
 * @alias syn-status
 * @copyright Copyright (C) 1998 - 2026 7x and the Exponential Foundation. All rights reserved.
 * @license GNU General Public License v2.0 (or any later version)
 * @package syndication
 */

require_once dirname( __FILE__ ) . '/../../../../autoload.php';

// The code is in extension/syndication/classes/runnable/commands/php_status.php (#207); this file is the entry point.
\Exponential\Command\Extension\Syndication\Status::main( __FILE__ );
