<?php
/**
 * The state and the log lines of a run in the background belong to the user who started it. Reads the sources and
 * calls eZSyndicationJob::belongsTo(), without a kernel or a database:
 *
 *   php vendor/bin/phpunit extension/syndication/tests/eZSyndicationJobAccessTest.php
 *
 *  JA-01 - belongsTo(): only the stored user id of the job; a job without one belongs to nobody, nor to anonymous 0
 *  JA-02 - syndication/job asks eZSyndicationJob::accessible() before it reads the job
 *  JA-03 - start() stores the user id, and the export and import commands keep it when they write the state again
 *
 * @copyright Copyright (C) 1998 - 2026 Exponential Foundation and 7x. All rights reserved.
 * @license GNU General Public License v2.0 (or any later version)
 * @package tests
 */

use PHPUnit\Framework\TestCase;

require_once dirname( __DIR__ ) . '/classes/ezsyndicationjob.php';

class eZSyndicationJobAccessTest extends TestCase
{
    private static function source( $path )
    {
        $source = (string)file_get_contents( dirname( __DIR__ ) . '/' . $path );
        return (string)preg_replace( '#^\s*//.*$#m', '', $source );
    }

    /** JA-01 */
    public function testAJobBelongsToTheUserWhoStartedIt()
    {
        $this->assertTrue( eZSyndicationJob::belongsTo( array( 'user_id' => 14 ), 14 ) );
        $this->assertTrue( eZSyndicationJob::belongsTo( array( 'user_id' => '14' ), 14 ) );
        $this->assertFalse( eZSyndicationJob::belongsTo( array( 'user_id' => 14 ), 15 ) );
        $this->assertFalse( eZSyndicationJob::belongsTo( array( 'by' => 'admin' ), 14 ), 'a job without a user id belongs to nobody' );
        $this->assertFalse( eZSyndicationJob::belongsTo( array( 'user_id' => 0 ), 0 ) );
        $this->assertFalse( eZSyndicationJob::belongsTo( false, 14 ) );
    }

    /** JA-02 */
    public function testTheViewAsksBeforeItReads()
    {
        $this->assertMatchesRegularExpression( '/eZSyndicationJob::accessible\(\s*\$jobID\s*\)\s*\?\s*eZSyndicationJob::status\(/',
                                               self::source( 'modules/syndication/job.php' ) );
        $class = self::source( 'classes/ezsyndicationjob.php' );
        $this->assertSame( 1, preg_match( '/static function accessible\(.*?\n    \}/s', $class, $m ) );
        $this->assertStringContainsString( 'self::belongsTo( $state, static::currentUserID() )', $m[0] );
        $this->assertStringContainsString( 'return (int)eZUser::currentUserID();', $class );
    }

    /** JA-03 */
    public function testTheOwnerIsStoredAndKept()
    {
        $this->assertMatchesRegularExpression( "/'user_id' => \(int\)\\\$user->attribute\( 'contentobject_id' \)/", self::source( 'classes/ezsyndicationjob.php' ) );
        foreach ( array( 'php_export.php' => 2, 'php_import.php' => 1 ) as $file => $writes )
        {
            $command = self::source( 'classes/runnable/commands/' . $file );
            $this->assertSame( $writes, substr_count( $command, 'eZSyndicationJob::write(' ), $file );
            $this->assertSame( $writes, substr_count( $command, "'user_id' => \$state && isset( \$state['user_id'] ) ? (int)\$state['user_id'] : 0" ), $file );
        }
    }
}
