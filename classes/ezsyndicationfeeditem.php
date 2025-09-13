<?php
//
// Definition of eZSyndicationFeedItem class
//
// Created on: <31-May-2006 15:52:38 hovik>
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

/*! \file ezsyndicationfeeditem.php
*/

/*!
  \class eZSyndicationFeedItem ezsyndicationfeeditem.php
  \brief The class eZSyndicationFeedItem does

*/

class eZSyndicationFeedItem extends eZPersistentObject
{
    const DOWNLOAD_OBJECT_DEFINITION_MAX_COUNT = 3;
    /*!
     Constructor
    */
    function __construct( $row = array() )
    {
        parent::__construct( $row );
    }

    static function definition()
    {
        return array( "fields" => array( "id" => array( 'name' => 'ID',
                                                        'datatype' => 'integer',
                                                        'default' => 0,
                                                        'required' => true ),
                                         "feed_id" => array( 'name' => 'FeedID',
                                                             'datatype' => 'integer',
                                                             'default' => 0,
                                                             'required' => true ),
                                         "host_id" => array( 'name' => 'HostID',
                                                             'datatype' => 'string',
                                                             'default' => '',
                                                             'required' => true ),
                                         'depth' => array( 'name' => 'Depth',
                                                           'datatype' => 'string',
                                                           'default' => '',
                                                           'required' => true ),
                                         'remote_id' => array( 'name' => 'RemoteID',
                                                               'datatype' => 'string',
                                                               'default' => '',
                                                               'required' => true ),
                                         'contentobject_version' => array( 'name' => 'ContentObjectVersion',
                                                                           'datatype' => 'integer',
                                                                           'default' => 0,
                                                                           'required' => true ),
                                         'options' => array( 'name' => 'Options',
                                                             'datatype' => 'string',
                                                             'default' => '',
                                                             'required' => true ),
                                         "modified" => array( 'name' => 'Modified',
                                                              'datatype' => 'integer',
                                                              'default' => 0,
                                                              'required' => true ) ),
                      "keys" => array( "id" ),
                      "function_attributes" => array( 'syndication_import' => 'syndicationImport',
                                                      'feed_item_status' => 'feedItemStatus',
                                                      'option_array' => 'optionArray' ),
                      "increment_key" => "id",
                      "class_name" => "eZSyndicationFeedItem",
                      "sort" => array( "id" => "desc" ),
                      "name" => "ezsyndication_feed_item" );
    }

    /*!
     Fetch object.

     \param feed item id
     \param asObject
    */
    static function fetch( $id,
                    $asObject = true )
    {
        return eZSyndicationFeedItem::fetchObject( eZSyndicationFeedItem::definition(),
                                                   null,
                                                   array( 'id' => $id ),
                                                   $asObject );
    }

    /*!
     \reimp
    */
    function attribute( $attr, $noFunction = false )
    {
        $retVal = null;
        switch( $attr )
        {
            case 'feed_item_status':
            {
                $retVal = eZSyndicationFeedItemStatus::fetchByFeedItemID( $this->attribute( 'id' ) );
            } break;

            case 'option_array':
            {
                $optionDef = $this->attribute( 'options' );
                $retVal = $optionDef == '' ? array() : unserialize( $optionDef );
            } break;

            case 'syndication_import':
            {
                $retVal = eZSyndicationImport::fetchByFeedHostID( $this->attribute( 'feed_id' ),
                                                                  $this->attribute( 'host_id' ) );
            } break;

            default:
            {
                $retVal = eZPersistentObject::attribute( $attr, $noFunction );
            } break;
        }

        return $retVal;
    }

    /*!
     Download Object definition from server.

     \param relatedRemoteID, set if downloading related object. ( optional )

     \return $objectDefinition string, false if something fails.
    */
    function downloadObjectDefinition( $relatedRemoteID = false )
    {
        $feedItemStatus = $this->attribute( 'feed_item_status' );

        if ( !$syndicationImport = $this->attribute( 'syndication_import' ) )
        {
            eZDebug::writeDebug( 'Could not load syndication import, feed ID: ' . $this->attribute( 'feed_id' ) );
            $feedItemStatus->setAttribute( 'status', eZSyndicationFeedItemStatus::STATUS_FAILED );
            $feedItemStatus->sync();
            return false;
        }

        $soapClient = $syndicationImport->attribute( 'soap_client' );
        if ( $relatedRemoteID )
        {
            $request = new eZSOAPRequest( "fetchSyndicationFeedRelatedContentObject",
                                          "http://ez.no/syndication",
                                          array( 'feedID' => $this->attribute( 'feed_id' ),
                                                 'remoteID' => $this->attribute( 'remote_id' ),
                                                 'relatedRemoteID' => $relatedRemoteID ) );
        }
        else
        {
            $request = new eZSOAPRequest( "fetchSyndicationFeedContentObject",
                                          "http://ez.no/syndication",
                                          array( 'feedID' => $this->attribute( 'feed_id' ),
                                                 'remoteID' => $this->attribute( 'remote_id' ) ) );
        }
        // Try to fetch object definition up to 3 times if it fails. Wait 2 seconds between each time
        $success = false;
        $objectDefinition = false;
        for ( $tryCount = 0;
              $tryCount < eZSyndicationFeedItem::DOWNLOAD_OBJECT_DEFINITION_MAX_COUNT;
              ++$tryCount )
        {
            $response = $soapClient->send( $request );
            if ( $response->faultCode() != false )
            {
                sleep( 2 );
                continue;
            }
            $objectDefinition = $response->value();
            if ( !$objectDefinition )
            {
                sleep( 2 );
                continue;
            }
            $success = true;
            break;
        }
        if ( !$success )
        {
            eZDebug::writeError( 'Unable to download object definition for item feed: ' . $this->attribute( 'id' ) );
            $feedItemStatus->setAttribute( 'status', eZSyndicationFeedItemStatus::STATUS_PENDING );
            $feedItemStatus->store();
            return false;
        }
        if ( in_array( $objectDefinition, array( eZSyndicationFeedCacheManager::OBJECT_DENIED,
                                                 eZSyndicationFeedCacheManager::OBJECT_INVALID,
                                                 eZSyndicationFeedCacheManager::OBJECT_DELETED ) ) )
        {
            switch( $objectDefinition )
            {
                case eZSyndicationFeedCacheManager::OBJECT_DENIED:
                {
                    $feedItemStatus->setAttribute( 'status', eZSyndicationFeedItemStatus::STATUS_DENIED );
                } break;

                case eZSyndicationFeedCacheManager::OBJECT_DELETED:
                {
                    $feedItemStatus->setAttribute( 'status', eZSyndicationFeedItemStatus::STATUS_DELETED );
                } break;

                default:
                case eZSyndicationFeedCacheManager::OBJECT_INVALID:
                {
                    $feedItemStatus->setAttribute( 'status', eZSyndicationFeedItemStatus::STATUS_FAILED );
                } break;
            }
            $feedItemStatus->store();

            return false;
        }

        $objectDefinition = base64_decode( $objectDefinition );
        return $objectDefinition;
    }

    /*!
     Import Object specified in feed item

     \param object definition string ( optional )
     \param options ( optional )
     \param is related object. ( optional )

     \return Returns false if something fails.
    */
    function import( $objectDefinition = false,
                     $options = array(),
                     $isRelatedObject = false )
    {
        $db = eZDB::instance();
        $db->begin();
        $feedItemStatus = $this->attribute( 'feed_item_status' );

        if ( !$isRelatedObject )
        {
	    $feedItemStatus->setAttribute( 'status', eZSyndicationFeedItemStatus::STATUS_INSTALLING );
            $feedItemStatus->sync();
        }

        $syndicationImport = $this->attribute( 'syndication_import' );
        $optionDefinitionList = array( 'auto_import' => 'automaticImport',
                                       'original_placement' => 'originalPlacement',
                                       'exclude_top_node' => 'excludeTopNode',
                                       'include_related_objects' => 'includeRelatedObjects',
                                       'use_hidden_status' => 'useHiddenStatus' );
        foreach( $optionDefinitionList as $optionKey => $optionName )
        {
            $$optionName = isset( $options[$optionKey] ) ? $options[$optionKey] : $syndicationImport->option( $optionKey );
        }
        $this->setOption( 'use_hidden_status', $useHiddenStatus );

        if ( !$objectDefinition )
        {
            $objectDefinition = $this->downloadObjectDefinition();
        }

        if ( !$objectDefinition )
        {
            eZDebug::writeError( 'Unable to fetch object definition, feed item: ' . $this->attribute( 'id' ) );
            $feedItemStatus->setOption( 'error' ,
                                        'Unable to fetch object definition from syndication server' );
            $feedItemStatus->setAttribute( 'status', eZSyndicationFeedItemStatus::STATUS_FAILED );
            $feedItemStatus->sync();
            $db->commit();
            return false;
        }

        $dom = new DOMDocument( '1.0', 'utf-8' );
        $success = $dom->loadXML( $objectDefinition );
        if ( !$success )
        {
            eZDebug::writeDebug( $objectDefinition, 'unable to parse object definition' );
            return false;
        }
        $domRoot = $dom->documentElement;

        // Fix XML object node.
        $objectNode = $this->fixNodeAssignments( $domRoot, $syndicationImport, $isRelatedObject );
        if ( !$objectNode )
        {
            $feedItemStatus->setOption( 'error',
                                        'Unable to resolve parent object.' );
            $feedItemStatus->setAttribute( 'status', eZSyndicationFeedItemStatus::STATUS_FAILED );
            $feedItemStatus->sync();
            $db->commit();
            return false;
        }

        // If use hidden status, set node assignments hidden status to the items status fields
        // Hidden status array format, array( <remote_id> => array( 'is_hidden' => <hidden value>,
        //                                                          'is_invisible' => <visible value> ) )
        $hiddenStatusArray = array();
        if ( $useHiddenStatus )
        {
            if ( $hiddenInformationNode = $domRoot->getElementsByTagName( 'hidden-info' )->item( 0 ) )
            {
                foreach( $hiddenInformationNode->getElementsByTagName( 'node' ) as $hiddenNode )
                {
                    $hiddenStatusArray[$hiddenNode->getAttribute( 'remote_id' )] =
                        array( 'is_hidden' => $hiddenNode->getAttribute( 'is_hidden' ),
                               'is_invisible' => $hiddenNode->getAttribute( 'is_invisible' ) );
                }
            }
        }
        $this->setOption( 'hidden_status_array', $hiddenStatusArray );

        // Check if it's top node, and should be excluded
        $nodeInformationNode = $domRoot->getElementsByTagName( 'node-info' )->item( 0 );
        if ( $excludeTopNode &&
             $nodeInformationNode->getAttribute( 'is-top-node' ) )
        {
            eZDebug::writeNotice( 'Ignoring top node' );
            $feedItemStatus->setOption( 'error',
                                        'Object is top node, and top nodes are ignored in import.' );
            $feedItemStatus->setAttribute( 'status', eZSyndicationFeedItemStatus::STATUS_IGNORED );
            $feedItemStatus->sync();
            $db->commit();
            return true;
        }

        // Store simple files.
        $simpleFileListNode = $domRoot->getElementsByTagName( 'simple-file-list' )->item( 0 );
        $this->storeSimpleFileList( $simpleFileListNode );

        $options = array( 'non-interactive' => true );
        $contentObject = eZContentObject::unserialize( $this, $objectNode, $options );
        $this->cleanupSimpleFileList();

        if ( $retVal = $contentObject instanceof eZContentObject )
        {
            $this->appendImportObjectID( $contentObject->attribute( 'id' ) );

            // Check if related objects should be imported.
            if ( $includeRelatedObjects )
            {
                if ( $relatedContentNodes = $domRoot->getElementsByTagName( 'related-object-list' )->item( 0 ) )
                {
                    foreach( $relatedContentNodes->getElementsByTagName( 'related-object' ) as $relatedContentNode )
                    {
                        $relatedRemoteID = $relatedContentNode->textContent;
                        $relatedObjectDefinition = $this->downloadObjectDefinition( $relatedRemoteID );

                        $this->import( $relatedObjectDefinition,
                                       array( 'include_related_objects' => false ),
                                       true );
                    }
                }
            }

            if ( !$isRelatedObject )
            {
                $feedItemStatus->setAttribute( 'status', eZSyndicationFeedItemStatus::STATUS_INSTALLED );
            }
        }
        else if ( !$isRelatedObject )
        {
            $feedItemStatus->setOption( 'error',
                                        'Unable to serialize content object.' );
            $feedItemStatus->setAttribute( 'status', eZSyndicationFeedItemStatus::STATUS_FAILED );
        }
        if ( !$isRelatedObject )
        {
            $feedItemStatus->store();
        }
        unset( $objectNode, $simpleFileListNode, $domRoot, $dom );

        $db->commit();
        return $retVal;
    }

    /*!
     Perform post import operations ( set related objects, hidden status, etc. ).
    */
    function postImport()
    {
        foreach( $this->ObjectImportIDList as $objectID )
        {
            $contentObject = eZContentObject::fetch( $objectID );
            $contentObject->postUnserialize( $this );

            // If configured, set node hidden status
            if ( $this->option( 'use_hidden_status' ) )
            {
                $hiddenStatusArray = $this->option( 'hidden_status_array' );
                foreach( $contentObject->assignedNodes() as $contentNode )
                {
                    $remoteID = $contentNode->attribute( 'remote_id' );
                    if ( isset( $hiddenStatusArray[$remoteID] ) )
                    {
                        $contentNode->setAttribute( 'is_hidden', $hiddenStatusArray[$remoteID]['is_hidden'] );
                        $contentNode->setAttribute( 'is_invisible', $hiddenStatusArray[$remoteID]['is_invisible'] );
                        $contentNode->sync();
                        eZContentObjectTreeNode::clearViewCacheForSubtree( $contentNode );
                    }
                }
            }
        }
    }

    /*!
     Get number of imported objects

     \return number of imported objects
    */
    function importObjectCount()
    {
        return  count( $this->ObjectImportIDList );
    }

    /*!
     */
    function cleanupSimpleFileList()
    {
        foreach( $this->SimpleFileArray as $key => $filename )
        {
            @unlink( $filename );
        }
        $this->SimpleFileArray = array();
    }

    /*!
     Store package files to disk.

     \param 'simple-file-list' dom node
    */
    function storeSimpleFileList( $simpleFileListNode )
    {
        $this->SimpleFileArray = array();
        foreach( $simpleFileListNode->getElementsByTagName( 'simple-file' ) as $simpleFileNode )
        {
            $filename = md5( $simpleFileNode->getAttribute( 'key' ) ) . '.' . $simpleFileNode->getAttribute( 'suffix' );
            $path = eZSyndicationFeedCacheManager::simpleFilePath();
            eZFile::create( $filename, $path, base64_decode( $simpleFileNode->textContent ) );
            $this->SimpleFileArray[$simpleFileNode->getAttribute( 'key' )] = eZDir::path( array( $path, $filename ) );
        }
    }

    /*!
     \private
     If no parent node exists in system, make sure object is imported to specified feed root.

     \param Serialied eZContentObject
     \param eZSyndicationImport obect
     \param isRelatedObject ( optional )

     \return $objectDomNode, false if adding node assignment fails.
    */
    function fixNodeAssignments( $domRoot,
                                 $syndicationImport,
                                 $isRelatedObject = false )
    {
        $oldNodeAssignment = false;
        $objectNode = $domRoot->getElementsByTagName( 'object' )->item( 0 );
        if ( !$objectNode )
        {
            return false;
        }

        $nodeInfo = $domRoot->getElementsByTagName( 'node-info' )->item( 0 );
        $nodeLevel = (int)$nodeInfo->getAttribute( 'level' );
        $excludeTopNode = $syndicationImport->option( 'exclude_top_node' );
        if ( $excludeTopNode )
        {
            --$nodeLevel;
        }
        $isTopNode = ( $nodeLevel == 1 );

        if ( $versionList = $objectNode->getElementsByTagName( 'version-list' )->item( 0 ) )
        {
            $versions = $versionList->getElementsByTagName( 'version' );
            foreach( $versions as $version )
            {
                $nodeAssignmentList = $version->getElementsByTagName( 'node-assignment-list' )->item( 0 );
                $newNodeAssignmentList = array();
                foreach( $nodeAssignmentList->getElementsByTagName( 'node-assignment' ) as $nodeAssignment )
                {
                    $parentRemoteID = $nodeAssignment->getAttribute( 'parent-node-remote-id' );
                    $contentNode = eZContentObjectTreeNode::fetchByRemoteID( $parentRemoteID, false );
                    if ( $contentNode &&
                         !$isTopNode )
                    {
                        $newNodeAssignmentList[] = $nodeAssignment;
                    }
                    else
                    {
                        $oldNodeAssignment = $nodeAssignment;
                    }
                }
                $nodeAssignmentNodes = $nodeAssignmentList->childNodes;

                for ( $i = $nodeAssignmentNodes->length - 1; $i >= 0; $i-- )
                {
                    $nodeAssignmentNode = $nodeAssignmentNodes->item( $i );
                    if ( $nodeAssignmentNode->nodeType == XML_ELEMENT_NODE && $nodeAssignmentNode->tagName == 'node-assignment' )
                    {
                        $nodeAssignmentList->removeChild( $nodeAssignmentNode );
                    }
                }

                // If no parent node assignments exists, alter an old one, with a new parent-node-remote-id
                if ( count( $newNodeAssignmentList ) == 0 )
                {
                    if ( $isRelatedObject )
                    {
                        $placementNode = $syndicationImport->attribute( 'related_node' );
                    }
                    else
                    {
                        if ( $isTopNode )
                        {
                            $placementNode = $syndicationImport->attribute( 'placement_node' );
                        }
                        else
                        {
                            $placementNode = false;
                        }
                    }
                    if ( !$placementNode )
                    {
                        eZDebug::writeError( 'eZSyndicationFeedItem::fixNodeAssignments(); Unable to fetch placement node, import item:' . $this->attribute( 'id' ) );
                        return false;
                    }
                    $oldNodeAssignment->removeAttribute( 'parent-node-remote-id' );
                    $oldNodeAssignment->setAttribute( 'parent-node-remote-id', $placementNode->attribute( 'remote_id' ) );
                    $nodeAssignmentList->appendChild( $oldNodeAssignment );
                }
                else
                {
                    foreach( $newNodeAssignmentList as $nodeAssignment )
                    {
                        $nodeAssignmentList->appendChild( $nodeAssignment );
                    }
                }
            }
        }

        return $objectNode;
    }

    /*!
     \static
     Create

     \param feedID
     \param eZContentObjectTreeNode
    */
    static function create( $feedID,
                     $hostID,
                     $depth,
                     $remoteID,
                     $objectVersion,
                     $options,
                     $modified )
    {
        return new eZSyndicationFeedItem( array( 'feed_id' => $feedID,
                                                 'host_id' => $hostID,
                                                 'depth' => $depth,
                                                 'remote_id' => $remoteID,
                                                 'contentobject_version' => $objectVersion,
                                                 'options' => $options,
                                                 'modified' => $modified ) );
    }

    /*!
     \static
     Fetch list based on feed ID and min modified TS
     Sorted so highest level is returned first.

     \param FeedID
     \param minimum modified ID ( optional )

     \return eZSyndicationFeedItem list
    */
    static function feedItemListByFeedID( $feedID, $modified = false, $asObject = true )
    {
        $condArray = array( 'feed_id' => $feedID );
        if ( $modified !== false )
        {
            $condArray['modified'] = array( '>', $modified );
        }
        return eZPersistentObject::fetchObjectList( eZSyndicationFeedItem::definition(),
                                                    null,
                                                    $condArray,
                                                    array( 'depth' => 'asc' ),
                                                    null,
                                                    $asObject );
    }

    /*!
     \static
     Get maximum modified value.

     \param FeedID ( optional )
     \param HostID
    */
    static function maxModified( $hostID,
                          $feedID )
    {
        $resultSet = eZSyndicationFeedItem::fetchObject( eZSyndicationFeedItem::definition(),
                                                         array(),
                                                         array( 'host_id' => $hostID,
                                                                'feed_id' => $feedID ),
                                                         false,
                                                         null,
                                                         array( array( 'operation' => 'max( modified )',
                                                                       'name' => 'max_modified' ) ) );
        if ( $resultSet )
        {
            return $resultSet['max_modified'];
        }
        return 0;
    }

    /*!
     \static
     Fetch object by contentobject

     \param FeedID
     \param eZContentObject

     \return eZSyndicationFeedItem object
    */
    static function fetchByContentObject( $feedID, $contentObject, $asObject = true )
    {
        return eZSyndicationFeedItem::fetchObject( eZSyndicationFeedItem::definition(),
                                                   null,
                                                   array( 'remote_id' => $contentObject->attribute( 'remote_id' ) ),
                                                   $asObject );
    }

    /*!
     \static
     Fetch object by host, feed_id and remote id.

     \param HostID
     \param FeedID
     \param eZContentObject

     \return eZSyndicationFeedItem object
    */
    static function fetchByHostFeedRemoteID( $hostID,
                                      $feedID,
                                      $remoteID,
                                      $asObject = true )
    {
        return eZSyndicationFeedItem::fetchObject( eZSyndicationFeedItem::definition(),
                                                   null,
                                                   array( 'remote_id' => $remoteID,
                                                          'feed_id' => $feedID,
                                                          'host_id' => $hostID ),
                                                   $asObject );
    }

    /*!
     \reimp
    */
    function store($fieldFilters = null)
    {
        eZPersistentObject::store( $filedFilters );
    }

    /*!
     Set option

     \param option name
     \param option value
    */
    function setOption( $attr, $value )
    {
        $optionArray = $this->attribute( 'option_array' );
        $optionArray[$attr] = $value;
        $this->setAttribute( 'options', serialize( $optionArray ) );
    }

    /*!
     Set option

     \param option name
     \param option valueArray
    */
    function setOptionArray( $valueArray )
    {
        $optionArray = array_merge( $this->attribute( 'option_array' ), $valueArray );
        $this->setAttribute( 'options', serialize( $optionArray ) );
    }

    /*
     Get option

     \param option name

     \return option value
    */
    function option( $attr )
    {
        $optionArray = $this->attribute( 'option_array' );
        return isset( $optionArray[$attr] ) ? $optionArray[$attr] : false;
    }

    /*!
     \reimp
     Wrapper funtion to fake this class as an eZPackage class.
    */
    function simpleFilePath( $key )
    {
        return isset( $this->SimpleFileArray[$key] ) ? $this->SimpleFileArray[$key] : false;
    }

    /*!
     Append object id to imported objects

     \param contentobject id
    */
    function appendImportObjectID( $objectID )
    {
        $this->ObjectImportIDList[] = $objectID;
    }

    var $SimpleFileArray = array();
    var $ObjectImportIDList = array();
}

?>
