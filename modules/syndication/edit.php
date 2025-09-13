<?php
//
// Created on: <13-Sep-2004 20:11:40 hovik>
//
// Copyright (C) 1999-2008 eZ Systems AS. All rights reserved.
//
// This source file is part of the eZ Publish (tm) Open Source Content
// Management System.
//
// This file may be distributed and/or modified under the terms of the
// "GNU General Public License" version 2 as published by the Free
// Software Foundation and appearing in the file LICENSE.GPL included in
// the packaging of this file.
//
// Licencees holding valid "eZ Publish professional licences" may use this
// file in accordance with the "eZ Publish professional licence" Agreement
// provided with the Software.
//
// This file is provided AS IS with NO WARRANTY OF ANY KIND, INCLUDING
// THE WARRANTY OF DESIGN, MERCHANTABILITY AND FITNESS FOR A PARTICULAR
// PURPOSE.
//
// The "eZ Publish professional licence" is available at
// http://ez.no/products/licences/professional/. For pricing of this licence
// please contact us via e-mail to licence@ez.no. Further contact
// information is available at http://ez.no/home/contact/.
//
// The "GNU General Public License" (GPL) is available at
// http://www.gnu.org/copyleft/gpl.html.
//
// Contact licence@ez.no if any conditions of this licencing isn't clear to
// you.
//

/*! \file edit.php
*/

$module =& $Params['Module'];
$feedID = $Params['FeedID'];
$http = eZHTTPTool::instance();

$syndicationFeed = eZSyndicationFeed::fetchDraft( $feedID );

if ( $http->hasPostVariable( 'Store' ) ||
     $http->hasPostVariable( 'AddSourceButton' ) ||
     $http->hasPostVariable( 'RemoveSourceButton' ) )
{
    $syndicationFeed->setAttribute( 'name', $http->postVariable( 'Name' ) );
    $syndicationFeed->setAttribute( 'identifier', $http->postVariable( 'Identifier' ) );
    $syndicationFeed->setAttribute( 'public_comment', $http->postVariable( 'PublicDescription' ) );
    $syndicationFeed->setAttribute( 'object_expiry_time', $http->postVariable( 'ObjectExpiryTime' ) );
    $syndicationFeed->setAttribute( 'enabled', ( $http->hasPostVariable( 'Active' ) ? 1 : 0 ) );
    $syndicationFeed->setAttribute( 'force_cronjob_cache', ( $http->hasPostVariable( 'ForceCronjobCache' ) ? 1 : 0 ) );
    $syndicationFeed->setAttribute( 'cache_timeout', $http->postVariable( 'CacheTimeout' ) );
    $syndicationFeed->sync();
}
if ( $http->hasPostVariable( 'Store' ) )
{
    $syndicationFeed->publish();
    return $module->redirectToView( 'list' );
}
else if ( $http->hasPostVariable( 'AddSourceButton' ) )
{
    return $module->redirectToView( 'add_feed_source', array( $feedID ) );
}
else if ( $http->hasPostVariable( 'RemoveSourceButton' ) )
{
    foreach ( $http->postVariable( 'RemoveSourceIDArray' ) as $sourceID )
    {
        eZSyndicationFeedSource::removeSource( $sourceID );
    }
}
else if ( $http->hasPostVariable( 'Cancel' ) )
{
    $syndicationFeed->removeDraft();
    return $module->redirectToView( 'list' );
}

include_once( "kernel/common/template.php" );
$tpl = templateInit();
$tpl->setVariable( 'syndication_feed', $syndicationFeed );

$Result = array();
$Result['content'] = $tpl->fetch( "design:syndication/edit.tpl" );
$Result['path'] = array( array( 'url' => 'syndication/menu',
                                'text' => ezpI18n::tr( 'syndication/list', 'Menu' ) ),
                         array( 'url' => 'syndication/list',
                                'text' => ezpI18n::tr( 'syndication/list', 'List' ) ),
                         array( 'url' => false,
                                'text' => ezpI18n::tr( 'syndication/list', 'Edit' ) ) );

?>
