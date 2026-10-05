<?php
/**
 * @copyright Copyright (C) 1998 - 2026 7x and the Exponential Foundation. All rights reserved.
 * @license GNU General Public License v2.0 (or any later version)
 * @package syndication
 */

namespace Exponential\Command\Extension\Syndication
{

/**
 * ext:syndication:install - create the tables of the extension from share/db_schema.dba.
 */
class Install extends \Exponential\Runnable\Command
{
    public function run()
    {
        $this->script( array( 'description' => 'Create the tables of the syndication extension',
                              'use-session' => false, 'use-modules' => true, 'use-extensions' => true ) );
        $options = $this->startup( '[dry-run]', '', array( 'dry-run' => 'Say which tables are missing, create nothing' ) );
        $missing = \eZSyndicationInstaller::missingTables();
        if ( $options['dry-run'] )
        {
            $this->output( $missing ? 'Would create: ' . implode( ', ', $missing ) : 'The tables exist.' );
            $this->shutdown( 0 );
            return 0;
        }
        $messages = array();
        $ok = \eZSyndicationInstaller::install( null, $messages );
        $this->output( implode( "\n", $messages ) );
        $this->shutdown( $ok ? 0 : 1 );
        return $ok ? 0 : 1;
    }
}

}
