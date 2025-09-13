<?php
//
// Created on: <11-Oct-2004 16:18:25 hovik>
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

/*! \file initialize.php
*/

/*!
  \brief The class Initialize does

*/

$server->registerFunction( 'fetchSyndicationFeedObjectList',
                           array( 'feedID' => 'integer' ) );
$server->registerFunction( 'fetchSyndicationFeedList' );
$server->registerFunction( 'fetchSyndicationFeedContentObject',
                           array( 'feedID' => 'integer',
                                  'remoteID' => 'string' ) );
$server->registerFunction( 'fetchSyndicationFeedRelatedContentObject',
                           array( 'feedID' => 'integer',
                                  'remoteID' => 'string',
                                  'relatedRemoteID' => 'string' ) );
$server->registerFunction( 'fetchSyndicationFeedItemList',
                           array( 'feedID' => 'integer',
                                  'modified' => 'integer' ) );
$server->registerFunction( 'hostID',
                           array() );

/*!
 Fetch list of latest Feed items given the specified feed id
*/
function fetchSyndicationFeedItemList( $feedID, $modified )
{
    return serialize( eZSyndicationFeedItemExport::feedItemListByFeedID( $feedID,
                                                                         $modified,
                                                                         false ) );
}

/*!
 Fetch feed list.
 */
function fetchSyndicationFeedList()
{
    $syndicationList = eZSyndicationFeed::fetchList( 0, 50 );

    $doc = new DOMDocument( '1.0', 'utf-8' );
    $rootNode = $doc->createElement( 'feed_list' );
    $doc->appendChild( $rootNode );

    foreach ( $syndicationList as $syndicationFeed )
    {
        $feedDom = $syndicationFeed->serializeSummary();
        $importedNode = $doc->importNode( $feedDom->documentElement, true );
        $rootNode->appendChild( $importedNode );
    }

    return $doc->saveXML();
    //return $doc->toString();
}

/*!
 Fetch syndication feed object

 \param $feedID
 \param $contentObjectRemoteID
*/
function fetchSyndicationFeedContentObject( $feedID, $remoteID )
{
    $contents = eZSyndicationFeedCacheManager::readObjectCache( $feedID, $remoteID );

    // escape xml entities, so they are not recognized as entities by DOM
    $contents = str_replace( '&', '&amp;', $contents );
    return $contents;
}

/*!
 */
function fetchSyndicationFeedRelatedContentObject( $feedID, $remoteID, $relatedRemoteID )
{
    return eZSyndicationFeedCacheManager::readRelatedObjectCache( $feedID, $remoteID, $relatedRemoteID );
}

/*!
 Fetch objects from specified list
*/
function fetchSyndicationFeedObjectList( $feedID )
{
    $syndicationFeed = eZSyndicationFeed::fetch( $feedID );

    return $syndicationFeed->feedList();
}

function hostID()
{
    return eZSyndicationFeedItemExport::localHostID();
}

?>
