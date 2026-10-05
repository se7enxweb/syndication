<?php
/**
 * @copyright Copyright (C) 1998 - 2026 7x and the Exponential Foundation. All rights reserved.
 * @license GNU General Public License v2.0 (or any later version)
 * @package syndication
 */

namespace Exponential\Command\Extension\Syndication
{

/**
 * ext:syndication:export - write the export cache of the active feeds (the cronjob part export_feed, by hand).
 */
class Export extends \Exponential\Runnable\Command
{
    public function run()
    {
        $this->script( array( 'description' => 'Write the export cache of the syndication feeds',
                              'use-session' => false, 'use-modules' => true, 'use-extensions' => true ) );
        $options = $this->startup( '[dry-run][feed:][max-objects:][job:]', '',
            array( 'dry-run' => 'Count what would be written, write nothing',
                   'feed' => 'Only the feed with this ID, active or not',
                   'max-objects' => 'Stop after this many written objects (0: no limit)',
                   'job' => 'The ID of a background job (set by the admin)' ) );
        $jobID = ( $options['job'] && \eZSyndicationJob::isID( $options['job'] ) ) ? $options['job'] : false;
        $out = new \eZSyndicationJobOutput( $jobID ? false : $this->cli(), $jobID );
        $user = \eZSyndicationRunner::loginCronUser();
        if ( $jobID )
        {
            $state = \eZSyndicationJob::status( $jobID );
            \eZSyndicationJob::write( $jobID, array( 'id' => $jobID, 'command' => 'export', 'status' => 'running', 'started' => $state ? $state['started'] : time(),
                                                      'by' => $state && isset( $state['by'] ) ? $state['by'] : 'job', 'result' => null ) );
        }
        if ( \eZSyndicationInstaller::missingTables() )
        {
            $out->error( 'The tables are missing; run ext:syndication:install first.' );
            $this->finishJob( $jobID, 'failed', array( 'error' => 'The tables are missing.' ) );
            $this->shutdown( 1 );
            return 1;
        }
        $totals = \eZSyndicationRunner::runExport( $out, $jobID ? 'job' : 'console', (int)$options['feed'], (bool)$options['dry-run'], (int)$options['max-objects'] );
        if ( $totals['locked'] )
        {
            $out->error( 'Another export is running.' );
            $this->finishJob( $jobID, 'failed', array( 'error' => 'Another export is running.' ) );
            $this->shutdown( 1 );
            return 1;
        }
        $out->output( ( $options['dry-run'] ? 'Would write ' : 'Written ' ) . $totals['generated'] . ' objects of ' . $totals['checked'] . ' checked, ' . $totals['feeds'] . ' feeds.' );
        $this->finishJob( $jobID, 'done', $totals );
        $this->shutdown( 0 );
        return 0;
    }

    protected function finishJob( $jobID, $status, $result )
    {
        if ( $jobID )
        {
            $state = \eZSyndicationJob::status( $jobID );
            \eZSyndicationJob::write( $jobID, array( 'id' => $jobID, 'command' => 'export', 'status' => $status,
                                                      'started' => $state ? $state['started'] : time(), 'finished' => time(),
                                                      'by' => $state && isset( $state['by'] ) ? $state['by'] : 'job', 'result' => $result ) );
            \eZSyndicationJob::prune();
        }
    }
}

}
