<?php
//
// Definition of eZSyndicationFeedCacheManager class
//
// Created on: <30-Jan-2005 15:26:23 hovik>
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

/*! \file ezsyndicationfeedcachemanager.php
*/

/*!
  \class eZSyndicationFeedCacheManager ezsyndicationfeedcachemanager.php
  \brief The class eZSyndicationFeedCacheManager provides cache functionality for syndication.

*/

class eZSyndicationFeedCacheManager
{
    const OBJECT_DENIED = 1;
    const OBJECT_DELETED = 2;
    const OBJECT_INVALID = 3;

    /*!
     Constructor. Load Syndication cache info.

     \param feed ID
    */
    function __construct( $feedID )
    {
        $this->CacheFileName .= $feedID . '.php';
        $this->BaseCachePath = eZDir::path( array( eZSys::storageDirectory(), 'syndication', 'feed' ) );
        $filename = eZDir::path( array( $this->BaseCachePath, $this->CacheFileName ) );
        $fileContents = false;
        if ( file_exists( $filename ) )
        {
            $fileContents = eZFile::getContents( $filename );
        }
        if ( $fileContents )
        {
            $this->CacheInfo = unserialize( $fileContents );
        }
    }

    /*!
     \static
     Get simple image tmp path
    */
    static function simpleFilePath()
    {
        return eZDir::path( array( eZSys::cacheDirectory(), 'syndication' ) );
    }

    /*!
     \static
     Initialize cache manager for specified feed id

     \param feed ID

     \return cache manager object
    */
    static function initialize( $feedID )
    {
        return new eZSyndicationFeedCacheManager( $feedID );
    }

    /*!
     Get cache creation TS from specified Content object

     \param remote id.

     \return false if no cache file exists, TS if it exists.
    */
    function objectCacheTS( $remoteID )
    {
        if ( isset ( $this->CacheInfo[$remoteID] ) )
        {
            return $this->CacheInfo[$remoteID]['cache_ts'];
        }
        return false;
    }

    /*!
     Cache and store cache for object.

     \param remoteID
     \param eZSyndicationFeedSource object.
     \param include related objects ( optional, default true )
     \param isRelated ( if object is related object, default false )
    */
    function cacheObject( $remoteID,
                          $exportSource,
                          $includeRelated = true,
                          $isRelated = false )
    {
        $this->SimpleFileListKey = $remoteID;
        $this->SimpleFileList[$this->SimpleFileListKey] = array();
        $contentObject = eZContentObject::fetchByRemoteID( $remoteID );

        if ( $contentObject )
        {
            $dom = new DOMDOcument( '1.0', 'utf-8' );

            $syndicationDOMNode = $dom->createElement( 'syndication' );
            $dom->appendChild( $syndicationDOMNode );
            if ( $includeRelated )
            {
                $relatedObjectNodeList = $dom->createElement( 'related-object-list' );
                $relatedObjectList = $contentObject->relatedContentObjectList();
                foreach( $relatedObjectList as $relatedObject )
                {
                    // Add object as related object.
                    $relatedObjectNodeList->appendChild( $dom->createElement( 'related-object', $relatedObject->attribute( 'remote_id' ) ) );
                    // Check if related object already has been cached.
                    $cacheTS = $this->objectCacheTS( $remoteID );
                    if ( !$cacheTS ||
                         $cacheTS < $relatedObject->attribute( 'modified' ) )
                    {
                        $prevSimpleFileListKey = $this->SimpleFileListKey;
                        $this->cacheObject( $relatedObject->attribute( 'remote_id' ),
                                            $exportSource,
                                            false,
                                            true );
                        $this->SimpleFileListKey = $prevSimpleFileListKey;
                    }
                }
                $syndicationDOMNode->appendChild( $relatedObjectNodeList );
            }
            // Set hidden status of node assignments
            // hidden-status => ( 'node_remote_id' => <node remote_id>, 'is_hidden' => <value>, 'is_invisible' => <value> )
            $hiddenInfoElement = $dom->createElement( 'hidden-info' );
            foreach ( $contentObject->assignedNodes() as $contentNode )
            {
                $nodeElement = $dom->createElement( 'node' );
                $nodeElement->setAttribute( 'remote_id', $contentNode->attribute( 'remote_id' ) );
                $nodeElement->setAttribute( 'is_hidden', $contentNode->attribute( 'is_hidden' ) );
                $nodeElement->setAttribute( 'is_invisible', $contentNode->attribute( 'is_invisible' ) );
                $hiddenInfoElement->appendChild( $nodeElement );
            }
            $syndicationDOMNode->appendChild( $hiddenInfoElement );

            $nodeInfoNode =  $dom->createElement( 'node-info' );
            $syndicationDOMNode->appendChild( $nodeInfoNode );

            // Related object status
            $nodeInfoNode->setAttribute( 'is-top-node', ( $exportSource->nodeLevel( $contentObject ) == 1 ) ? '1' : '0' );
            if ( $isRelated )
            {
                $nodeInfoNode->setAttribute( 'is_related', 1 );
            }
            else
            {
                $nodeInfoNode->setAttribute( 'level', $exportSource->nodeLevel( $contentObject ) );
                $nodeInfoNode->setAttribute( 'is_related', 0 );
            }

            $objectNode = $contentObject->serialize( $this,
                                                     true,
                                                     array( 'language_array' => eZContentObject::translationStringList(),
                                                            'node_assignment' => 'main',
                                                            'related_objects' => false ),
                                                     array( $contentObject->attribute( 'main_node_id' ) => $contentObject->attribute( 'main_node' ) ),
                                                     array() );

            $importedObjectNode = $dom->importNode( $objectNode, true );
            $syndicationDOMNode->appendChild( $importedObjectNode );

            $simpleFileListNode = $this->createSimpleFileListDomNode();
            $importedSimpleFileListNode = $dom->importNode( $simpleFileListNode, true );
            $syndicationDOMNode->appendChild( $importedSimpleFileListNode );

            $dom->formatOutput = true;
            $objectCachePath = $this->objectCachePath( $remoteID );
            eZFile::create( $remoteID, $objectCachePath, $dom->saveXML() );
            unset( $syndicationDOMNode );
        }
        $this->CacheInfo[$remoteID] = array( 'cache_ts' => time(),
                                             'is_related' => $isRelated,
                                             'file_list' => $this->SimpleFileList[$this->SimpleFileListKey],
                                             'version' => $contentObject->attribute( 'current_version' ),
                                             'name' => $contentObject->attribute( 'name' ) );
        $this->InfoChanged = true;
    }

    /*!
     Create dom node from simpleFileList
    */
    function createSimpleFileListDomNode()
    {
        $dom = new DOMDocument();
        $simpleFileListDomNode = $dom->createElement( 'simple-file-list' );
        foreach( $this->SimpleFileList[$this->SimpleFileListKey] as $key => $filename )
        {
            $simpleFileNode = $dom->createElement( 'simple-file', base64_encode( eZFile::getContents( $filename ) ) );
            $simpleFileNode->setAttribute( 'key', $key );
            $simpleFileNode->setAttribute( 'suffix', eZFile::suffix( $filename ) );
            $simpleFileListDomNode->appendChild( $simpleFileNode );
        }

        return $simpleFileListDomNode;
    }

    /*!
     Get object cache path based on remote id

     \param remoteID

     \return object cache path
    */
    function objectCachePath( $remoteID )
    {
        return eZDir::path( array( $this->BaseCachePath, eZDir::getPathFromFilename( $remoteID ) ) );
    }

    /*!
     \static
     Read serialized object from object Cache.

     \param FeedID
     \param RemoteID

     \return Serialized object
    */
    function readObjectCache( $feedID, $remoteID )
    {
        $syndicationCache = eZSyndicationFeedCacheManager::initialize( $feedID );
        $cacheInfo = $syndicationCache->cacheInfo( $remoteID );
        if ( !$cacheInfo )
        {
            if ( !$syndicationFeed = eZSyndicationFeed::fetch( $feedID ) )
            {
                return eZSyndicationFeedCacheManager::OBJECT_DENIED;
            }

            if ( !$contentObject = eZContentObject::fetchByRemoteID( $remoteID ) )
            {
                return eZSyndicationFeedCacheManager::OBJECT_DELETED;
            }
            if ( $feedSource = $syndicationFeed->fetchSourceByObject( eZContentObject::fetchByRemoteID( $remoteID ) ) )
            {
                $syndicationCache->cacheObject( $remoteID, $feedSource );
                $cacheInfo = $syndicationCache->cacheInfo( $remoteID );
            }
            if ( !$cacheInfo )
            {
                return eZSyndicationFeedCacheManager::OBJECT_INVALID;
            }
        }
        return eZFile::getContents( eZDir::path( array( $syndicationCache->objectCachePath( $remoteID ), $remoteID ) ) );
    }

    /*!
     \static
     Read serialized object from object Cache.

     \param FeedID
     \param RemoteID

     \return Serialized object
    */
    function readRelatedObjectCache( $feedID, $remoteID, $relatedRemoteID )
    {
        $syndicationCache = eZSyndicationFeedCacheManager::initialize( $feedID );
        $cacheInfo = $syndicationCache->cacheInfo( $relatedRemoteID );
        if ( !$cacheInfo )
        {
            if ( !$syndicationFeed = eZSyndicationFeed::fetch( $feedID ) )
            {
                return eZSyndicationFeedCacheManager::OBJECT_DENIED;
            }

            if ( !$baseObject = eZContentObject::fetchByRemoteID( $remoteID ) )
            {
                return eZSyndicationFeedCacheManager::OBJECT_DELETED;
            }

            if ( $feedSource = $syndicationFeed->fetchSourceByObject( eZContentObject::fetchByRemoteID( $remoteID ) ) )
            {
                if ( !$relatedObject = eZContentObject::fetchByRemoteID( $relatedRemoteID ) )
                {
                    return eZSyndicationFeedCacheManager::OBJECT_DENIED;
                }

                $isRelated = false;
                foreach( $baseObject->relatedContentObjectList() as $baseRelatedObject )
                {
                    if ( $baseRelatedObject->attribute( 'id' ) == $relatedObject->attribute( 'id' ) )
                    {
                        $isRelated = true;
                        break;
                    }
                }
                if ( !$isRelated )
                {
                    return eZSyndicationFeedCacheManager::OBJECT_DENIED;
                }

                $syndicationCache->cacheObject( $relatedRemoteID,
                                                $feedSource,
                                                false,
                                                true );
                $cacheInfo = $syndicationCache->cacheInfo( $relatedRemoteID );
            }

            if ( !$cacheInfo )
            {
                return eZSyndicationFeedCacheManager::OBJECT_INVALID;
            }
        }
        return eZFile::getContents( eZDir::path( array( $syndicationCache->objectCachePath( $relatedRemoteID ), $relatedRemoteID ) ) );
    }

    /*!
     Get Content object cache info

     \param remoteID

     \return cache info
    */
    function cacheInfo( $remoteID )
    {
        return isset( $this->CacheInfo[$remoteID] ) ? $this->CacheInfo[$remoteID] : false;
    }

    /*!
     Store Cache info
    */
    function storeInfo()
    {
        if ( $this->InfoChanged ||
             !file_exists( eZDir::path( array( $this->BaseCachePath, $this->CacheFileName ) ) ) )
        {
            eZFile::create( $this->CacheFileName, $this->BaseCachePath, serialize( $this->CacheInfo ) );
        }
    }

    /*!
     \reimp
     Wrapper funtion to fake this class as an eZPackage class.
    */
    function appendSimpleFile( $key, $filePath )
    {
        $this->SimpleFileList[$this->SimpleFileListKey][$key] = $filePath;
    }

    var $CacheFileName = 'feed_cache_info_';
    var $CacheInfo = array();
    var $BaseCachePath = '';
    var $SimpleFileList = array();
    var $SimpleFileListKey = '';
    var $InfoChanged = false;
}

?>
