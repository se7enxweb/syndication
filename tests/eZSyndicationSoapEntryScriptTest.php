<?php
/**
 * The SOAP server of Exponential is index_soap.php since 6.0.15 (soap.php before, a forwarding shim since). The
 * extension names index_soap.php where the kernel has it and soap.php on an older kernel. Reads the files only,
 * without a kernel or a database:
 *
 *   php vendor/bin/phpunit extension/syndication/tests/eZSyndicationSoapEntryScriptTest.php
 *
 *  SE-01 - eZSyndicationUI::soapEntryScript() is index_soap.php where it exists, soap.php otherwise
 *  SE-02 - the dashboard and the feed page take the address from eZSyndicationUI::soapURL()
 *  SE-03 - the templates and the translations give index_soap.php as the example, never soap.php
 *
 * @copyright Copyright (C) 1998 - 2026 Exponential Foundation and 7x. All rights reserved.
 * @license GNU General Public License v2.0 (or any later version)
 * @package tests
 */

use PHPUnit\Framework\TestCase;

require_once dirname( __DIR__ ) . '/classes/ezsyndicationui.php';

class eZSyndicationSoapEntryScriptTest extends TestCase
{
    /** @var string|null */
    private $dir;

    protected function tearDown(): void
    {
        if ( $this->dir !== null )
        {
            foreach ( array( 'index_soap.php', 'soap.php' ) as $file )
            {
                if ( is_file( $this->dir . '/' . $file ) )
                    unlink( $this->dir . '/' . $file );
            }
            rmdir( $this->dir );
            $this->dir = null;
        }
    }

    /** SE-01 */
    public function testTheEntryScriptFollowsTheKernel()
    {
        $this->dir = dirname( __DIR__ ) . '/var-test-soap-' . getmypid() . '-' . mt_rand();
        mkdir( $this->dir, 0700 );
        touch( $this->dir . '/soap.php' );
        $this->assertSame( 'soap.php', eZSyndicationUI::soapEntryScript( $this->dir ), 'a kernel older than 6.0.15' );
        touch( $this->dir . '/index_soap.php' );
        $this->assertSame( 'index_soap.php', eZSyndicationUI::soapEntryScript( $this->dir ), '6.0.15 and later' );
        $this->assertSame( 'index_soap.php', eZSyndicationUI::soapEntryScript( $this->dir . '/' ) );
    }

    /** SE-02 */
    public function testTheDashboardAndTheFeedPageUseSoapURL()
    {
        $root = dirname( __DIR__ );
        foreach ( array( 'classes/ezsyndicationdashboard.php', 'modules/syndication/feed_info.php' ) as $file )
        {
            $source = (string)file_get_contents( $root . '/' . $file );
            $this->assertStringContainsString( 'eZSyndicationUI::soapURL()', $source, $file );
            $this->assertStringNotContainsString( "'/soap.php'", $source, $file );
        }
    }

    /** SE-03 */
    public function testTheExamplesNameIndexSoapPhp()
    {
        $root = dirname( __DIR__ );
        $files = array_merge( array( $root . '/design/standard/templates/wizard/step_1.tpl',
                                     $root . '/design/standard/templates/syndication/import_edit/step_1.tpl' ),
                              glob( $root . '/translations/*/translation.ts' ) ?: array() );
        foreach ( $files as $file )
        {
            $source = (string)file_get_contents( $file );
            $this->assertDoesNotMatchRegularExpression( '#[/ ]soap\.php#', $source, basename( dirname( $file ) ) . '/' . basename( $file ) );
        }
        $this->assertStringContainsString( "'Server (example: https://example.com/index_soap.php)'|i18n( 'extension/syndication' )",
                                           (string)file_get_contents( $root . '/design/standard/templates/syndication/import_edit/step_1.tpl' ) );
    }
}
