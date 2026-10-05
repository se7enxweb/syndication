<?php
/**
 * @copyright Copyright (C) 1998 - 2026 7x and the Exponential Foundation. All rights reserved.
 * @license GNU General Public License v2.0 (or any later version)
 * @package syndication
 */

namespace Exponential\Command\Extension\Syndication
{

/**
 * ext:syndication:import - fetch the item lists of the imports and import the waiting items (the cronjob part
 * import_feed, by hand).
 */
class Import extends \Exponential\Runnable\Command
{
    public function run()
    {
        $this->script( array( 'description' => 'Fetch the syndication imports and import the waiting items',
                              'use-session' => false, 'use-modules' => true, 'use-extensions' => true ) );
        $options = $this->startup( '[dry-run][import:][fetch-only][import-only][limit:][job:]', '',
            array( 'dry-run' => 'Fetch nothing, count the waiting items',
                   'import' => 'Only the import with this ID, active or not',
                   'fetch-only' => 'Only fetch the item lists, import nothing',
                   'import-only' => 'Only import the waiting items, fetch nothing',
                   'limit' => 'Import at most this many items per import (default 10)',
                   'job' => 'The ID of a background job (set by the admin)' ) );
        $jobID = ( $options['job'] && \eZSyndicationJob::isID( $options['job'] ) ) ? $options['job'] : false;
        $out = new \eZSyndicationJobOutput( $jobID ? false : $this->cli(), $jobID );
        \eZSyndicationRunner::loginCronUser();
        $state = $jobID ? \eZSyndicationJob::status( $jobID ) : false;
        $write = function ( $status, $result ) use ( $jobID, $state )
        {
            if ( $jobID )
            {
                \eZSyndicationJob::write( $jobID, array( 'id' => $jobID, 'command' => 'import', 'status' => $status,
                                                          'started' => $state ? $state['started'] : time(), 'finished' => $status == 'running' ? null : time(),
                                                          'by' => $state && isset( $state['by'] ) ? $state['by'] : 'job', 'result' => $result ) );
                \eZSyndicationJob::prune();
            }
        };
        $write( 'running', null );
        if ( \eZSyndicationInstaller::missingTables() )
        {
            $out->error( 'The tables are missing; run ext:syndication:install first.' );
            $write( 'failed', array( 'error' => 'The tables are missing.' ) );
            $this->shutdown( 1 );
            return 1;
        }
        $mode = $options['fetch-only'] ? 'fetch' : ( $options['import-only'] ? 'import' : 'both' );
        $totals = \eZSyndicationRunner::runImport( $out, $jobID ? 'job' : 'console', (int)$options['import'], (bool)$options['dry-run'],
                                                    $options['limit'] ? max( 1, (int)$options['limit'] ) : 10, $mode );
        if ( $totals['locked'] )
        {
            $out->error( 'Another import is running.' );
            $write( 'failed', array( 'error' => 'Another import is running.' ) );
            $this->shutdown( 1 );
            return 1;
        }
        $out->output( $options['dry-run']
            ? 'Would fetch ' . $totals['imports'] . ' imports; ' . $totals['waiting'] . ' items wait.'
            : $totals['imports'] . ' imports: ' . $totals['new'] . ' new or changed items, ' . $totals['imported'] . ' imported, ' . $totals['failed'] . ' failed.' );
        $write( $totals['ok'] ? 'done' : 'failed', $totals );
        $this->shutdown( $totals['ok'] ? 0 : 1 );
        return $totals['ok'] ? 0 : 1;
    }
}

}
