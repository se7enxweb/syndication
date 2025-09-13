<?php
//
// Created on: <31-May-2006 15:08:00 hovik>
//
// Copyright (C) 1999-2008 eZ Systems AS. All rights reserved.
//
// This source file is part of the eZ Publish (tm) Open Source Content
// Management System.
//
// This file may be distributed and/or modified under the terms of the
// "GNU General Public License" version 2 as published by the Free
// Software Foundation and appearing in the file LICENSE included in
// the packaging of this file.
//
// Licencees holding a valid "eZ Publish professional licence" version 2
// may use this file in accordance with the "eZ Publish professional licence"
// version 2 Agreement provided with the Software.
//
// This file is provided AS IS with NO WARRANTY OF ANY KIND, INCLUDING
// THE WARRANTY OF DESIGN, MERCHANTABILITY AND FITNESS FOR A PARTICULAR
// PURPOSE.
//
// The "eZ Publish professional licence" version 2 is available at
// http://ez.no/ez_publish/licences/professional/ and in the file
// PROFESSIONAL_LICENCE included in the packaging of this file.
// For pricing of this licence please contact us via e-mail to licence@ez.no.
// Further contact information is available at http://ez.no/company/contact/.
//
// The "GNU General Public License" (GPL) is available at
// http://www.gnu.org/copyleft/gpl.html.
//
// Contact licence@ez.no if any conditions of this licencing isn't clear to
// you.
//

/*! \file syndication_import.php
*/

/*!
  \class Syndication_Import syndication_import.php
  \brief The class Syndication_Import does

*/

@ini_set( 'memory_limit', '512M' );

$db = eZDB::instance();

$syndicationINI = eZINI::instance( 'syndication.ini' );
$userID = $syndicationINI->variable( 'Syndication', 'CronUser' );
$user = eZUser::instance( $userID );
eZUser::setCurrentlyLoggedInUser( $user, $userID );

$offset = 0;
$limit = 10;

// Go through import list and fetch new feed items.
while( $syndicationImportList = eZSyndicationImport::fetchList( $offset, $limit ) )
{
    foreach( $syndicationImportList as $syndicationImport )
    {
        $db->begin();

        // Fetch new feed items, and create item status objects as well.
        $syndicationImport->fetchNewItems();

        $db->commit();
    }
    $offset += $limit;
}

// Go through all feed items with status 'pending', and install them
$offset = 0;
$limit = 10;
while( $feedItemStatusList = eZSyndicationFeedItemStatus::fetchList( array( array( eZSyndicationFeedItemStatus::STATUS_PENDING,
                                                                                   eZSyndicationFeedItemStatus::STATUS_FAILED ) ),
                                                                     $offset, $limit ) )
{
    foreach( $feedItemStatusList as $feedItemStatus )
    {
        $db->begin();
	if ( $feedItem = $feedItemStatus->attribute( 'feed_item' ) )
        {
            $result = $feedItem->import();

            if ( $result )
            {
                $objectCount = $feedItem->importObjectCount();
                $feedItem->postImport();
                $cli->output( 'Imported object: ' . $feedItem->attribute( 'remote_id' ) . ' ( ' . $objectCount . ' objects )' );
                $db->commit();
            }
            else
            {
                ++$offset;
                $cli->output( 'ContentObject import failed: ' . $feedItem->attribute( 'remote_id' ) );
                $db->rollback();
            }
        }
        else
        {
            // eZDebug::writeError( 'Could not find eZSyndicationFeedItem: ' . $feedItem->attribute( 'id' ) );
	    // eZDebug::writeError( 'Could not find eZSyndicationFeedItem: null' );
	    $cli->output( "Could not find eZSyndicationFeedItem feedItem in above conditional test was returned  null\n" );
	    exit;
        }
    }
}

?>