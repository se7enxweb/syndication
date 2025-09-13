<?php
//
// Definition of SyndicationExport class
//
// Created on: <10-Oct-2004 17:22:28 hovik>
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

/*! \file syndication_export.php
*/

/*!
  \brief Creates caches, etc for syndication module.
*/

@ini_set( 'memory_limit', '512M' );

$offset = 0;
$limit = 10;

$syndicationINI = eZINI::instance( 'syndication.ini' );
$userID = $syndicationINI->variable( 'Syndication', 'CronUser' );
$user = eZUser::instance( $userID );
eZUser::setCurrentlyLoggedInUser( $user, $userID );

$exportFeedList = eZSyndicationFeed::fetchList( $offset, $limit );
$progressArray = array( '-', '\\', '|', '/' );
$count = 0;

while( true )
{
    foreach( $exportFeedList as $exportFeed )
    {
        $cli->output( 'Processing export feed: ' . $exportFeed->attribute( 'name' ) );
        $feedID  = $exportFeed->attribute( 'id' );
        $cacheManager = eZSyndicationFeedCacheManager::initialize( $feedID );

        $exportSourceList = $exportFeed->sourceList();

        foreach( $exportSourceList as $exportSource )
        {
            $exportSourceNode = $exportSource->attribute( 'node' );
            $cli->output( '  Processing source : ' . $exportSourceNode->attribute( 'name' ) );
            foreach( $exportSource->remoteIDArray() as $remoteID => $contentNode )
            {
                $contentObject = $contentNode->attribute( 'object' );
                $generate = false;
                $cacheTS = $cacheManager->objectCacheTS( $remoteID );
                $cacheInfo = $cacheManager->cacheInfo( $remoteID );
                // Check if object is newer
                if ( !$cacheTS ||
                     $cacheTS < $contentObject->attribute( 'modified' ) )
                {
                    $generate = true;
                }
                // Check if object has only been stored as related object.
                else if ( $cacheInfo &&
                          $cacheInfo['is_related'] )
                {
                    $generate = true;
                }
                if ( $generate )
                {
                    if ( !$script->isQuiet() )
                    {
                        echo "\r " . $progressArray[++$count % 4];
                    }
                    $cacheManager->cacheObject( $remoteID, $exportSource );
                    eZSyndicationFeedItemExport::update( $feedID, $contentNode );
                }
            }
        }


        $cacheManager->storeInfo();
    }
    $offset += $limit;
    $exportFeedList = eZSyndicationFeed::fetchList( $offset, $limit );
    if ( !$exportFeedList )
    {
        break;
    }
}

echo "\n";

?>
