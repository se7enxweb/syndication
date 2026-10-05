<?php
/**
 * @copyright Copyright (C) 1998 - 2026 7x and the Exponential Foundation. All rights reserved.
 * @license GNU General Public License v2.0 (or any later version)
 * @package syndication
 */

namespace Exponential\Command\Extension\Syndication
{

/**
 * ext:syndication:status - what is exported, what is imported, the last runs and the problems found.
 */
class Status extends \Exponential\Runnable\Command
{
    public function run()
    {
        $this->script( array( 'description' => 'Show the state of the syndication feeds and imports',
                              'use-session' => false, 'use-modules' => true, 'use-extensions' => true ) );
        $this->startup();
        $summary = \eZSyndicationDashboard::summary();
        if ( $summary['tables_missing'] )
        {
            $this->output( 'Tables missing: ' . implode( ', ', $summary['tables_missing'] ) );
            $this->shutdown( 1 );
            return 1;
        }
        $this->output( 'Feeds:   ' . $summary['feeds']['total'] . ' (' . $summary['feeds']['enabled'] . ' active), ' . $summary['feeds']['sources'] . ' sources, ' . $summary['feeds']['items'] . ' exported items' );
        $this->output( 'Imports: ' . $summary['imports']['total'] . ' (' . $summary['imports']['enabled'] . ' active)' );
        $this->output( 'Items:   ' . json_encode( $summary['items'] ) );
        foreach ( array( 'export', 'import' ) as $part )
        {
            $run = $summary['runs'][$part];
            $this->output( 'Last ' . $part . ': ' . ( $run ? date( 'Y-m-d H:i:s', $run['time'] ) . ' by ' . $run['by'] : 'never' ) );
        }
        foreach ( $summary['problems'] as $problem )
        {
            $this->output( strtoupper( $problem['level'] ) . ': ' . $problem['text'] );
        }
        $this->shutdown( 0 );
        return 0;
    }
}

}
