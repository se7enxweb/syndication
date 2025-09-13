<?php
//
// Definition of eZSyndicationFeedSource class
//
// Created on: <12-Sep-2004 17:05:39 kk>
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

/*! \file ezsyndicationfeedsource.php
*/

/*!
  \class eZSyndicationFeedSource ezsyndicationfeedsource.php
  \brief The class eZSyndicationFeedSource does

*/

class eZSyndicationFeedSource extends eZPersistentObject
{
    const TYPE_NODE = 0;
    const TYPE_TREE = 1;

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
                                         'feed_id' => array( 'name' => 'FeedID',
                                                             'datatype' => 'integer',
                                                             'default' => 0,
                                                             'required' => true ),
                                         'node_id' => array( 'name' => 'NodeID',
                                                             'datatype' => 'integer',
                                                             'default' => 0,
                                                             'required' => true ),
                                         'type' => array( 'name' => 'Type',
                                                          'datatype' => 'integer',
                                                          'default' => 0,
                                                          'required' => true ) ),
                      'keys' => array( 'id', 'status' ),
                      'function_attributes' => array( 'filter_list' => 'filterList',
                                                      'draft_filter_list' => 'draftFilterList',
                                                      'node' => 'node',
                                                      'type_string' => 'typeString',
                                                      'feed' => 'feed' ),
                      'increment_key' => 'id',
                      'class_name' => 'eZSyndicationFeedSource',
                      'sort' => array( 'id' => 'asc' ),
                      'name' => 'ezsyndication_feed_source' );
    }

    /*!
     \reimp
    */
    function attribute( $attr, $noFunction = false )
    {
        $retVal = null;
        switch( $attr )
        {
            case 'feed':
            {
                $retVal = eZSyndicationFeed::fetch( $this->attribute( 'feed_id' ),
                                                    $this->attribute( 'status' ) );
            } break;

            case 'type_string':
            {
                switch( $this->attribute( 'type' ) )
                {
                    case eZSyndicationFeedSource::TYPE_NODE:
                    {
                        $retVal = ezpI18n::tr( 'syndication/edit', 'Node', 'Source type' );
                    } break;

                    case eZSyndicationFeedSource::TYPE_TREE:
                    {
                        $retVal = ezpI18n::tr( 'syndication/edit', 'Subtree', 'Source type' );
                    } break;

                    default:
                    {
                        $retVal = '';
                    } break;
                }
            } break;

            case 'node':
            {
                $retVal = eZContentObjectTreeNode::fetch( $this->attribute( 'node_id' ) );
            } break;

            case 'draft_filter_list':
            {
                $retVal = eZSyndicationFeedSourceFilter::fetchDraftList( $this->attribute( 'id' ) );
            } break;

            case 'filter_list':
            {
                $retVal = eZSyndicationFeedSourceFilter::fetchList( $this->attribute( 'id' ),
                                                                    0,
                                                                    100,
                                                                    $this->attribute( 'status' ) );
            } break;

            default:
            {
                $retVal = eZPersistentObject::attribute( $attr, $noFunction );
            } break;
        }

        return $retVal;
    }

    /*!
      \static
      Fetch draft of eZSyndicationFeed object. A new object is created if none exist.
     */
    static function fetchDraft( $id, $asObject = true )
    {
        $feedSource = eZSyndicationFeedSource::fetch( $id, eZSyndicationFeed::STATUS_DRAFT, $asObject );
        if ( !$feedSource )
        {
            $feedSource = eZSyndicationFeedSource::fetch( $id, eZSyndicationFeed::STATUS_PUBLISHED, $asObject );
            if ( $feedSource )
            {
                $feedSource->setAttribute( 'status', eZSyndicationFeed::STATUS_DRAFT );
                $feedSource->store();
            }
        }

        if ( !$feedSource )
        {
            $feedSource = eZSyndicationFeedSource::create();
        }
        return $feedSource;
    }

    /*!
     \static
     Fetch feed source

     \param source id
     \param status

     \return eZSyndicationFeedSource
    */
    static function fetch( $sourceID, $status = eZSyndicationFeed::STATUS_PUBLISHED, $asObject = true )
    {
        $condArray = array( 'id' => $sourceID );
        if ( $status !== false )
        {
            $condArray['status'] = $status;
        }
        return eZPersistentObject::fetchObject( eZSyndicationFeedSource::definition(),
                                                null,
                                                $condArray,
                                                $asObject );
    }

    /*!
     Get object level in regards to top node.
     Top node gets level 1.

     \return node level
    */
    function nodeLevel( $contentObject )
    {
        $node = $this->attribute( 'node' );
        foreach( $contentObject->attribute( 'assigned_nodes' ) as $objectNode )
        {
            if ( strpos( $objectNode->attribute( 'path_string' ), $node->attribute( 'path_string' ) ) === 0 )
            {
                return $objectNode->attribute( 'depth' ) - $node->attribute( 'depth' ) + 1;
            }
        }

        return 100;
    }

    /*!
     Create a new Feed Source

     \param Feed ID
     \param Node ID
     \param Feed type ( optional, default subtree )
    */
    static function create( $feedID,
                     $nodeID,
                     $type = eZSyndicationFeedSource::TYPE_TREE )
    {
        if ( !is_numeric( $type ) )
        {
            switch( $type )
            {
                default:
                case 'tree':
                {
                    $type = eZSyndicationFeedSource::TYPE_TREE;
                } break;
                case 'node':
                {
                    $type = eZSyndicationFeedSource::TYPE_NODE;
                } break;
            }
        }

        return new eZSyndicationFeedSource( array( 'feed_id' => $feedID,
                                                   'status' => eZSyndicationFeed::STATUS_DRAFT,
                                                   'node_id' => $nodeID,
                                                   'type' => $type ) );
    }

    /*!
     \static
     Fetch based on feed id

     \param feed id

     \return list of feed sources
    */
    static function fetchList( $feedID, $status = eZSyndicationFeed::STATUS_PUBLISHED )
    {
        return eZPersistentObject::fetchObjectList( eZSyndicationFeedSource::definition(),
                                                    null,
                                                    array( 'feed_id' => $feedID,
                                                           'status' => $status ) );
    }

    /*!
     Fetch list of source filters

     \return source filters for this feed
    */
    function &filterDraftList()
    {
        $result = eZSyndicationFeedSourceFilter::fetchDraftList( $this->attribute( 'id' ) );
        return $result;
    }

    /*!
     Create array of domnode objects for all export elements.

     \return array of domnodes with remote_id as keys
     */
    function remoteIDArray()
    {
        $limit = 50;
        $offset = 0;

        $returnArray = array();
        $filterList = $this->attribute( 'filter_list' );

        while( true )
        {
            $nodeArray = array();
            if ( $offset == 0 )
            {
                $nodeArray[] = eZContentObjectTreeNode::fetch( $this->attribute( 'node_id' ) );
            }

            if ( $this->attribute( 'type' ) == eZSyndicationFeedSource::TYPE_NODE ) // Node source type
            {
                if ( $offset != 0 )
                {
                    break;
                }
            }
            else // Subtree node type
            {
                $nodeArray = array_merge( $nodeArray,
                                          eZContentObjectTreeNode::subTreeByNodeID( array( 'Offset' => $offset,
                                                                                           'Limit' => $limit,
                                                                                           'Limitation' => array(),
                                                                                           'IgnoreVisibility' => true ),
                                                                                    $this->attribute( 'node_id' ) ) );
                if ( count( $nodeArray ) == 0 )
                {
                    break;
                }
            }

            foreach( array_keys( $nodeArray ) as $key )
            {
                $node = $nodeArray[$key];
                $nodeAccepted = true;
                if ( $filterList &&
                     count( $filterList ) > 0 )
                {
                    $nodeAccepted = false;
                    foreach( $filterList as $filter ) // check if node is accepted by one of the filters.
                    {
                        if ( $filter->filterObject( $node->attribute( 'object' ) ) )
                        {
                            $nodeAccepted = true;
                            break;
                        }
                    }
                }

                if ( $nodeAccepted )
                {
                    $contentObject = $node->attribute( 'object' );
                    $returnArray[$contentObject->attribute( 'remote_id' )] = $node;
                }
            }

            // Clear eZContentObject cache, and increase offset.
            eZContentObject::clearCache();
            $offset += $limit;
        }

        return $returnArray;
    }

    /*!
     \reimp
     Remove all feed sources according to feed ID.

     \param Source feed id
    */
    function removeSource( $id = false )
    {
        if ( $id !== false )
        {
            $source = eZSyndicationFeedSource::fetch( $id );
            if ( !$source )
            {
                $source = eZSyndicationFeedSource::fetch( $id, eZSyndicationFeed::STATUS_DRAFT );
            }
            if ( $source )
            {
                $source->removeSource();
            }
            return;
        }

        $this->removeDraft();

        foreach( $this->attribute( 'filter_list' ) as $filter )
        {
            $filter->removeFilter();
        }

        eZPersistentObject::remove();
    }

    /*!
      Publish eZSyndicationFeed object.
      Sets the status to published, stores the object and removes the draft version.
    */
    function publish()
    {
        foreach( $this->attribute( 'filter_list' ) as $filter )
        {
            $filter->publish();
        }

        $this->setAttribute( 'status', eZSyndicationFeed::STATUS_PUBLISHED );
        $this->store();
    }

    /*!
     Remove published.
    */
    function removePublish()
    {
        $published = eZSyndicationFeedSource::fetch( $this->attribute( 'id' ), eZSyndicationFeed::STATUS_PUBLISHED );

        if ( $published )
        {
            foreach( $published->attribute( 'filter_list' ) as $filter )
            {
                $filter->removePublish();
            }

            $published->remove();
        }
    }

    /*!
     Remove draft.
    */
    function removeDraft()
    {
        $draft = eZSyndicationFeedSource::fetchDraft( $this->attribute( 'id' ) );

        foreach( $draft->attribute( 'filter_list' ) as $filter )
        {
            $filter->removeDraft();
        }

        $draft->remove();
    }
}

?>
