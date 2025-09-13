<?php
//
// Definition of eZSyndicationImport class
//
// Created on: <12-Oct-2004 14:57:54 hovik>
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

/*! \file ezsyndicationimport.php
*/

/*!
  \class eZSyndicationImport ezsyndicationimport.php
  \brief The class eZSyndicationImport does

*/

class eZSyndicationImport extends eZPersistentObject
{
    const STATUS_DRAFT = 0;
    const STATUS_PUBLISHED = 1;

    /*!
     Constructor
    */
    function __construct( $row )
    {
        parent::__construct( $row );
    }

    /*!
     \reimp
    */
    static function definition()
    {
        return array( 'fields' => array( 'id' => array( 'name' => 'ID',
                                                        'datatype' => 'integer',
                                                        'default' => 0,
                                                        'required' => true ),
                                         'status' => array( 'name' => 'Status',
                                                            'datatype' => 'integer',
                                                            'default' => 0,
                                                            'required' => true ),
                                         'creator_id' => array( 'name' => 'CreatorID',
                                                                'datatype' => 'integer',
                                                                'default' => 0,
                                                                'required' => true ),
                                         'created_ts' => array( 'name' => 'CreatedTS',
                                                                'datatype' => 'integer',
                                                                'default' => 0,
                                                                'required' => true ),
                                         'enabled' => array( 'name' => 'Enabled',
                                                             'datatype' => 'integer',
                                                             'default' => 1,
                                                             'required' => true ),
                                         'feed_id' => array( 'name' => 'FeedID',
                                                             'datatype' => 'integer',
                                                             'default' => 0,
                                                             'required' => true ),
                                         'object_count' => array( 'name' => 'ObjectCount',
                                                                  'datatype' => 'integer',
                                                                  'default' => 1,
                                                                  'required' => true ),
                                         'name' => array( 'name' => 'Name',
                                                          'datatype' => 'string',
                                                          'default' => '',
                                                          'required' => true ),
                                         'server' => array( 'name' => 'Server',
                                                          'datatype' => 'string',
                                                          'default' => '',
                                                          'required' => true ),
                                         'host_id' => array( 'name' => 'HostID',
                                                             'datatype' => 'string',
                                                             'default' => '',
                                                             'required' => true ),
                                         'comment' => array( 'name' => 'Comment',
                                                             'datatype' => 'string',
                                                             'default' => '',
                                                             'required' => true ),
                                         'placement_node_id' => array( 'name' => 'PlacementNodeID',
                                                                       'datatype' => 'integer',
                                                                       'default' => 0,
                                                                       'required' => true ),
                                         'related_node_id' => array( 'name' => 'RelatedNodeID',
                                                                     'datatype' => 'integer',
                                                                     'default' => 0,
                                                                     'required' => true ),
                                         'options' => array( 'name' => 'Options',
                                                             'datatype' => 'string',
                                                             'default' => '',
                                                             'required' => true ) ),
                      'keys' => array( 'id', 'status' ),
                      'function_attributes' => array( 'can_remove' => 'canRemove',
                                                      'placement_node' => 'placementNode',
                                                      'related_node' => 'relatedNode',
                                                      'option_array' => 'optionArray',
                                                      'can_create' => 'canCreate',
                                                      'can_edit' => 'canEdit',
                                                      'pending_count' => 'pendingCount',
                                                      'failed_count' => 'failedCount',
                                                      'installed_count' => 'installedCount',
                                                      'installing_count' => 'installingCount',
                                                      'none_count' => 'noneCount',
                                                      'filter_list' => 'filterList',
                                                      'server_host' => 'serverHost',
                                                      'server_port' => 'serverPort',
                                                      'server_path' => 'serverPath',
                                                      'soap_client' => 'soapClient' ),
                      'increment_key' => 'id',
                      'class_name' => 'eZSyndicationImport',
                      'sort' => array( 'name' => 'asc' ),
                      'name' => 'ezsyndication_import' );
    }

    /*!
     \reimp
    */
    function attribute( $attr, $noFunction = false )
    {
        $retVal = null;
        switch( $attr )
        {
            case 'soap_client':
            {
                $retVal = new eZSOAPClient( $this->attribute( 'server_host' ),
                                            $this->attribute( 'server_path' ),
                                            $this->attribute( 'server_port' ),
                                            $this->attribute( 'server_scheme' ) === 'https' );
            } break;

            case 'installed_count':
            case 'installing_count':
            case 'none_count':
            case 'pending_count':
            case 'failed_count':
            {
                switch( $attr )
                {
                    case 'failed_count':
                    {
                        $status = eZSyndicationFeedItemStatus::STATUS_FAILED;
                    } break;

                    case 'pending_count':
                    {
                        $status = eZSyndicationFeedItemStatus::STATUS_PENDING;
                    } break;

                    case 'installing_count':
                    {
                        $status = eZSyndicationFeedItemStatus::STATUS_INSTALLING;
                    } break;

                    case 'installed_count':
                    {
                        $status = eZSyndicationFeedItemStatus::STATUS_INSTALLED;
                    } break;

                    default:
                    case 'none_count':
                    {
                        $status = eZSyndicationFeedItemStatus::STATUS_NONE;
                    } break;
                }

                $db = eZDB::instance();
                $sql = 'SELECT count( status.id ) as count
                        FROM ezsyndication_feed_item item,
                             ezsyndication_feed_item_status status
                        WHERE item.feed_id = \'' . $db->escapeString( $this->attribute( 'feed_id' ) ) . '\' AND
                              item.id = status.feed_item_id AND
                              status.status = \'' . $db->escapeString( $status ) . '\'';
                $result = $db->arrayQuery( $sql );

                $retVal = $result[0]['count'];
            } break;

            case 'server_host':
            case 'server_port':
            case 'server_path':
            case 'server_scheme':
            {
                $url = parse_url( $this->attribute( 'server' ) );
                $defaultArray = array( 'host' => '',
                                       'port' => 80,
                                       'path' => '/' );
                $attr = substr( $attr, 7 );
                $retVal = isset( $url[$attr] ) ? $url[$attr] : $defaultArray[$attr];
            } break;

            case 'placement_node':
            {
                $retVal = eZContentObjectTreeNode::fetch( $this->attribute( 'placement_node_id' ) );
            } break;

            case 'related_node':
            {
                $retVal = eZContentObjectTreeNode::fetch( $this->attribute( 'related_node_id' ) );
            } break;

            case 'filter_list':
            {
                $retVal = eZSyndicationImportFilter::fetchList( $this->attribute( 'id' ),
                                                                $this->attribute( 'status' ) );
            } break;

            case 'option_array':
            {
                $optionDef = $this->attribute( 'options' );
                $retVal = $optionDef == '' ? array() : unserialize( $optionDef );
            } break;

            case 'can_edit':
            case 'can_remove':
            case 'can_create':
            {
                $retVal = true;
            } break;

            default:
            {
                $retVal = eZPersistentObject::attribute( $attr, $noFunction );
            } break;
        }

        return $retVal;
    }

    /*!
     Fetch new Feed items from server.
    */
    function fetchNewItems()
    {
        $soapClient = $this->attribute( 'soap_client' );
        $maxModified = eZSyndicationFeedItem::maxModified( $this->attribute( 'host_id' ),
                                                           $this->attribute( 'feed_id' ) );
        $requestParams = array( 'feedID' => $this->attribute( 'feed_id' ) );
        if ( !is_null( $maxModified ) )
        {
            $requestParams['modified'] = $maxModified;
        }
        $request = new eZSOAPRequest( "fetchSyndicationFeedItemList",
                                      "http://ez.no/syndication",
                                      $requestParams );

        $response = $soapClient->send( $request );
        if ( $response->faultCode() != false )
        {
            return array();
        }

        $feedExportItem = unserialize( $response->value() );

        foreach( $feedExportItem as $feedExport )
        {
            if ( $existingFeed = eZSyndicationFeedItem::fetchByHostFeedRemoteID( $this->attribute( 'host_id' ),
                                                                                 $this->attribute( 'feed_id' ),
                                                                                 $feedExport['remote_id'] ) )
            {
                if ( $feedExport['modified'] > $existingFeed->attribute( 'modified' ) )
                {
                    if ( $existingFeed->attribute( 'contentobject_version' ) != $feedExport['contentobject_version'] )
                    {
                        $feedItemStatus = $existingFeed->attribute( 'feed_item_status' );
                        $feedItemStatus->setAttribute( 'status', $this->itemStatus() );
                        $feedItemStatus->store();
                    }
                    $existingFeed->setAttribute( 'depth', $feedExport['depth'] );
                    $existingFeed->setAttribute( 'contentobject_version', $feedExport['contentobject_version'] );
                    $existingFeed->setAttribute( 'options', $feedExport['options'] );
                    $existingFeed->setAttribute( 'modified', $feedExport['modified'] );
                    $existingFeed->store();
                }
            }
            else
            {
                $feedItem = eZSyndicationFeedItem::create( $feedExport['feed_id'],
                                                           $feedExport['host_id'],
                                                           $feedExport['depth'],
                                                           $feedExport['remote_id'],
                                                           $feedExport['contentobject_version'],
                                                           $feedExport['options'],
                                                           $feedExport['modified'] );
                $feedItem->store();

                $feedItemStatus = eZSyndicationFeedItemStatus::create( $feedItem->attribute( 'id' ) );
                $feedItemStatus->setAttribute( 'status', $this->itemStatus() );
                $feedItemStatus->store();
            }
        }
    }

    /*!
     Get item status status
    */
    function itemStatus()
    {
        if ( $this->option( 'auto_import' ) )
        {
            return eZSyndicationFeedItemStatus::STATUS_PENDING;
        }

        return eZSyndicationFeedItemStatus::STATUS_NONE;
    }

    /*!
     \static

     Fetch list of item statuses.

     \param status ( eZSyndicationFeedItemStatus status types )
     \param offset
     \param limit

     \return Item status list
     */
    function fetchItemStatusList( $status = eZSyndicationFeedItemStatus::STATUS_NONE,
                                  $offset = 0,
                                  $limit = 10,
                                  $asObject = true )
    {
        $db = eZDB::instance();

        if ( is_array( $status ) )
        {
            $statusString = ' IN ( \'';
            $statusString .= implode( '\', \'', $status );
            $statusString .= '\' ) ';
        }
        else
        {
            $statusString = ' = \'' . $db->escapeString( $status ) . '\'';
        }
        $sql = 'SELECT status.*
                FROM ezsyndication_feed_item item,
                     ezsyndication_feed_item_status status
                WHERE item.feed_id = \'' . $db->escapeString( $this->attribute( 'feed_id' ) ) . '\' AND
                      item.id = status.feed_item_id AND
                      status.status ' . $statusString . '
                ORDER BY item.modified DESC';
        $resultSet = $db->arrayQuery( $sql,
                                      array( 'offset' => $offset,
                                             'limit' => $limit ) );
        return eZSyndicationFeedItemStatus::handleRows( $resultSet,
                                                        'eZSyndicationFeedItemStatus',
                                                        $asObject );
    }

    /*!
    \static

    Fetch count of list of item statuses.

    \param status ( eZSyndicationFeedItemStatus status types )
    \param offset
    \param limit

    \return Item status list
    */
    function fetchItemStatusListCount( $status = eZSyndicationFeedItemStatus_StatusNone )
    {
        $db = eZDB::instance();

        if ( is_array( $status ) )
        {
            $statusString = ' IN ( \'';
            $statusString .= implode( '\', \'', $status );
            $statusString .= '\' ) ';
        }
        else
        {
            $statusString = ' = \'' . $db->escapeString( $status ) . '\'';
        }
        $sql = 'SELECT COUNT(status.id) status_count
             FROM ezsyndication_feed_item item,
                  ezsyndication_feed_item_status status
             WHERE item.feed_id = \'' . $db->escapeString( $this->attribute( 'feed_id' ) ) . '\' AND
                   item.id = status.feed_item_id AND
                   status.status ' . $statusString . '
             ORDER BY item.modified DESC';
        $resultSet = $db->arrayQuery( $sql );
        return $resultSet[0]['status_count'];
    }

    static function canCreate()
    {
        return true;
    }

    static function canRemove()
    {
        return true;
    }

    /*!
     \static
     Create new syndication import

     \return Syndication import
    */
    static function create()
    {
        $user = eZUser::instance();
        return new eZSyndicationImport( array( 'creator_id' => $user->attribute( 'contentobject_id' ),
                                               'status' => eZSyndicationImport::STATUS_DRAFT,
                                               'created_ts' => time() ) );
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
     \static
     Fetch syndication import

     \param import id
     \param status, default published
    */
    static function fetch( $id,
                    $status = eZSyndicationImport::STATUS_PUBLISHED )
    {
        $condArray = array( 'id' => $id );
        if ( $status !== false )
        {
            $condArray['status'] = $status;
        }
        return eZPersistentObject::fetchObject( eZSyndicationImport::definition(),
                                                   null,
                                                   $condArray,
                                                   array( 'limit' => 1 ) );
    }

    /*!
     \static
     Fetch by feed ID

     \param FeedID
     \param HostID
     \param $asObject

     \return eZSyndicationImport
    */
    static function fetchByFeedHostID( $feedID,
                                $hostID,
                                $status = eZSyndicationImport::STATUS_PUBLISHED,
                                $asObject = true )
    {
        return eZPersistentObject::fetchObject( eZSyndicationImport::definition(),
                                                null,
                                                array( 'feed_id' => $feedID,
                                                       'host_id' => $hostID,
                                                       'status' => $status ),
                                                $asObject );
    }

    /*!
     \static
     Fetch syndication import list

     \param import offset ( default 0 )
     \param number of syndocation imports ( default 15 )
     \param status of syndication feed
    */
    static function fetchList( $offset = 0,
                        $limit = 15,
                        $status = eZSyndicationImport::STATUS_PUBLISHED )
    {
        return eZPersistentObject::fetchObjectList( eZSyndicationImport::definition(),
                                                    null,
                                                    array( 'status' => $status ),
                                                    null,
                                                    array( 'offset' => $offset,
                                                           'limit' => $limit ) );
    }

    /*!
     \reimp
     Remove current or specified import.

     \param import id, optional, current object if none specified
    */
    public static function removeImport( $ID = false )
    {
        if ( $ID !== false )
        {
            $import = eZSyndicationImport::fetch( $ID );
            if ( $import )
            {
                $import->remove();
            }
            return;
        }

        foreach( $this->attribute( 'filter_list' ) as $filter )
        {
            $filter->remove();
        }

        eZPersistentObject::remove();
    }

    /*!
      \static
      Fetch draft of eZSyndicationImport object. A new object is created if none exist.
     */
    static function fetchDraft( $id, $asObject = true )
    {
        $draft = eZSyndicationImport::fetch( $id, eZSyndicationImport::STATUS_DRAFT, $asObject );
        if ( !$draft )
        {
            $draft = eZSyndicationImport::fetch( $id, eZSyndicationImport::STATUS_PUBLISHED, $asObject );
            if ( $draft )
            {
                $draft->setAttribute( 'status', eZSyndicationImport::STATUS_DRAFT );
                $draft->store();
            }
        }

        if ( !$draft )
        {
            $draft = eZSyndicationImport::create();
        }
        return $draft;
    }

    /*!
    */
    function publish()
    {
        foreach( $this->attribute( 'filter_list' )as $filter )
        {
            $filter->publish();
        }

        $this->setAttribute( 'status', eZSyndicationImport::STATUS_PUBLISHED );
        $this->store();
    }

}

?>
