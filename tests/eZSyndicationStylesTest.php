<?php
/**
 * The badges of the syndication pages are the shared .a4-badge (is-ok, is-warn, is-bad, is-info) of the administration's
 * components (components.css, design.ini AdminComponentCSSFileList), which every admin design loads before syndication.css.
 * Reads the files only, without a kernel or a database:
 *
 *   php vendor/bin/phpunit extension/syndication/tests/eZSyndicationStylesTest.php
 *
 *  ST-01 - the templates and the job script use .a4-badge, no pill of their own, and no is-muted
 *  ST-02 - syndication.css draws no pill or badge of its own
 *
 * @copyright Copyright (C) 1998 - 2026 7x & Exponential Foundation. All rights reserved.
 * @license GNU General Public License v2.0 (or any later version)
 * @package tests
 */

use PHPUnit\Framework\TestCase;

class eZSyndicationStylesTest extends TestCase
{
    private static function root()
    {
        return dirname( __DIR__ ) . '/design/standard';
    }

    /** ST-01 */
    public function testTheTemplatesUseTheSharedBadge()
    {
        $files = array_merge( glob( self::root() . '/templates/*/*.tpl' ) ?: array(), glob( self::root() . '/templates/*/*/*.tpl' ) ?: array(),
                              glob( self::root() . '/templates/parts/*/*.tpl' ) ?: array(), array( self::root() . '/javascript/syndication_admin.js' ) );
        $badges = 0;
        foreach ( $files as $file )
        {
            $source = (string)file_get_contents( $file );
            $this->assertStringNotContainsString( 'syn-pill', $source, basename( $file ) );
            $this->assertStringNotContainsString( 'is-muted', $source, basename( $file ) );
            $badges += substr_count( $source, 'a4-badge' );
        }
        $this->assertGreaterThan( 0, $badges );
        $this->assertStringContainsString( "status.className = ('a4-badge ' + (classes[job.status] || '')).trim();",
                                            (string)file_get_contents( self::root() . '/javascript/syndication_admin.js' ) );
    }

    /** ST-02 */
    public function testTheStylesheetDrawsNoPillOfItsOwn()
    {
        $css = (string)file_get_contents( self::root() . '/stylesheets/syndication.css' );
        $this->assertStringNotContainsString( '.syn-pill', $css );
        // a rule of its own for .a4-badge; a layout rule inside its own markup (li span.a4-badge) is fine
        $css = (string)preg_replace( '#/\*.*?\*/#s', '', $css );
        $this->assertDoesNotMatchRegularExpression( '/(^|[\s,}])\.a4-badge\s*[{.,]/', $css, 'components.css draws the badge' );
    }
}
