<?php
/**
 * eZSyndicationJob::accessible() on real job files in a temporary folder. A subclass answers the two seams of the
 * class (directory(), currentUserID()), so the test needs no session and no database and declares no class of the
 * kernel: it runs alike with the kernel's bootstrap (phpunit.xml, suite syndication) and without it:
 *
 *   php vendor/bin/phpunit --testsuite syndication
 *   php vendor/bin/phpunit --no-configuration extension/syndication/tests/eZSyndicationJobAccessibleTest.php
 *
 *  JB-01 - a job id without a job file, and an id of the wrong form, are refused
 *  JB-02 - a job of another user is refused, its owner may read it
 *  JB-03 - a job written before the owner was stored (no user_id) is refused
 *
 * @copyright Copyright (C) 1998 - 2026 Exponential Foundation and 7x. All rights reserved.
 * @license GNU General Public License v2.0 (or any later version)
 * @package tests
 */

use PHPUnit\Framework\TestCase;

require_once dirname( __DIR__ ) . '/classes/ezsyndicationjob.php';

/** eZSyndicationJob with its folder and its user in memory */
class eZSyndicationJobAccessibleTestJob extends eZSyndicationJob
{
    public static $dir = '';
    public static $userID = 0;

    static function directory()
    {
        return self::$dir;
    }

    protected static function currentUserID()
    {
        return self::$userID;
    }
}

class eZSyndicationJobAccessibleTest extends TestCase
{
    const MINE = '20261020120000-0a1b2c3d';
    const OTHER = '20261020120001-0a1b2c3e';
    const LEGACY = '20261020120002-0a1b2c3f';

    private $dir;

    protected function setUp(): void
    {
        $this->dir = sys_get_temp_dir() . '/syndication-accessible-' . bin2hex( random_bytes( 4 ) );
        mkdir( $this->dir, 0700, true );
        eZSyndicationJobAccessibleTestJob::$dir = $this->dir;
        eZSyndicationJobAccessibleTestJob::$userID = 14;
        $jobs = array(
            self::MINE => array( 'user_id' => 14 ),
            self::OTHER => array( 'user_id' => 15 ),
            self::LEGACY => array( 'by' => 'admin' ),
        );
        foreach ( $jobs as $id => $state )
            file_put_contents( $this->dir . '/' . $id . '.json', json_encode( $state + array( 'id' => $id, 'command' => 'export', 'status' => 'done' ) ) );
    }

    protected function tearDown(): void
    {
        if ( $this->dir && is_dir( $this->dir ) )
        {
            array_map( 'unlink', glob( $this->dir . '/*' ) ?: array() );
            rmdir( $this->dir );
        }
    }

    /** JB-01 */
    public function testAJobThatDoesNotExistIsRefused()
    {
        $this->assertFileDoesNotExist( $this->dir . '/20261020999999-ffffffff.json' );
        $this->assertFalse( eZSyndicationJobAccessibleTestJob::accessible( '20261020999999-ffffffff' ) );
        $this->assertFalse( eZSyndicationJobAccessibleTestJob::accessible( '../' . self::MINE ) );
        $this->assertFalse( eZSyndicationJobAccessibleTestJob::accessible( '' ) );
    }

    /** JB-02 */
    public function testAJobOfAnotherUserIsRefused()
    {
        $this->assertTrue( eZSyndicationJobAccessibleTestJob::accessible( self::MINE ) );
        $this->assertFalse( eZSyndicationJobAccessibleTestJob::accessible( self::OTHER ) );
        eZSyndicationJobAccessibleTestJob::$userID = 15;
        $this->assertTrue( eZSyndicationJobAccessibleTestJob::accessible( self::OTHER ), 'its owner may' );
        $this->assertFalse( eZSyndicationJobAccessibleTestJob::accessible( self::MINE ) );
    }

    /** JB-03 */
    public function testAJobWithoutAnOwnerIsRefused()
    {
        $this->assertFalse( eZSyndicationJobAccessibleTestJob::accessible( self::LEGACY ) );
        eZSyndicationJobAccessibleTestJob::$userID = 0;
        $this->assertFalse( eZSyndicationJobAccessibleTestJob::accessible( self::LEGACY ), 'not even for anonymous 0' );
    }
}
