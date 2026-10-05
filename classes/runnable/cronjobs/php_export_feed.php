<?php
/**
 * @copyright Copyright (C) 1998 - 2026 7x and the Exponential Foundation. All rights reserved.
 * @license GNU General Public License v2.0 (or any later version)
 * @package syndication
 */

namespace Exponential\Cronjob\Extension\Syndication
{

/**
 * Cronjob part export_feed: writes the export cache of every active feed. runcronjobs.php runs the part's file
 * cronjobs/syndication_export.php, which is one call to this class. The console command
 * ext:syndication:export runs the same code.
 */
class ExportFeed extends \Exponential\Runnable\CronjobPart
{
    public function run( array $scope )
    {
        @ini_set( 'memory_limit', '512M' );
        $cli = isset( $scope['cli'] ) ? $scope['cli'] : \eZCLI::instance();
        \eZSyndicationRunner::loginCronUser();
        $totals = \eZSyndicationRunner::runExport( $cli, 'cron' );
        if ( $totals['locked'] )
        {
            $cli->output( 'Another export is running; this run does nothing.' );
            return false;
        }
        $cli->output( 'Export done: ' . $totals['feeds'] . ' feeds, ' . $totals['generated'] . ' objects written, ' . $totals['checked'] . ' checked.' );
        return true;
    }
}

}
