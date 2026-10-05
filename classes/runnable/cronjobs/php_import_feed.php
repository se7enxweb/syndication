<?php
/**
 * @copyright Copyright (C) 1998 - 2026 7x and the Exponential Foundation. All rights reserved.
 * @license GNU General Public License v2.0 (or any later version)
 * @package syndication
 */

namespace Exponential\Cronjob\Extension\Syndication
{

/**
 * Cronjob part import_feed: fetches the item list of every active import and imports the waiting items.
 * The console command ext:syndication:import runs the same code.
 */
class ImportFeed extends \Exponential\Runnable\CronjobPart
{
    public function run( array $scope )
    {
        @ini_set( 'memory_limit', '512M' );
        $cli = isset( $scope['cli'] ) ? $scope['cli'] : \eZCLI::instance();
        \eZSyndicationRunner::loginCronUser();
        $totals = \eZSyndicationRunner::runImport( $cli, 'cron' );
        if ( $totals['locked'] )
        {
            $cli->output( 'Another import is running; this run does nothing.' );
            return false;
        }
        $cli->output( 'Import done: ' . $totals['imports'] . ' imports, ' . $totals['new'] . ' new or changed items, ' . $totals['imported'] . ' imported, ' . $totals['failed'] . ' failed.' );
        return $totals['ok'];
    }
}

}
