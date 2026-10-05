#!/usr/bin/env php
<?php
/**
 * Create the tables of the syndication extension
 *
 * Usage:
 *   php extension/syndication/bin/php/install.php [options]   (or ./console ext:syndication:install)
 *   --help shows the options.
 *
 * @alias syn-install
 * @copyright Copyright (C) 1998 - 2026 7x and the Exponential Foundation. All rights reserved.
 * @license GNU General Public License v2.0 (or any later version)
 * @package syndication
 */

require_once dirname( __FILE__ ) . '/../../../../autoload.php';

// The code is in extension/syndication/classes/runnable/commands/php_install.php (#207); this file is the entry point.
\Exponential\Command\Extension\Syndication\Install::main( __FILE__ );
