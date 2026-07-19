<?php
//
// Definition of eZSyndicationFeed class
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

/*! \file ezsyndicationfeed.php
*/

/*!
  \class eZSyndicationFeed ezsyndicationfeed.php
  \brief The class eZSyndicationFeed is used to fetch and store syndication feeds.

*/

class eZSyndicationFeed extends eZPersistentObject
{
    const STATUS_DRAFT = 0;
    const STATUS_PUBLISHED = 1;
    public $CreatorID;
    public $CreatedTS;
    public $Enabled;
    public $CacheTimeout;
    public $ObjectExpiryTime;
    public $ForceCronjobCache;
    public $ObjectCount;
    public $PrivateComment;
    public $PublicComment;

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
                                                             'default' => 0,
                                                             'required' => true ),
                                         'object_expiry_time' => array( 'name' => 'ObjectExpiryTime',
                                                                        'datatype' => 'integer',
                                                                        'default' => 90,
                                                                        'required' => true ),
                                         'cache_timeout' => array( 'name' => 'CacheTimeout',
                                                                   'datatype' => 'integer',
                                                                   'default' => 10,
                                                                   'required' => true ),
                                         'force_cronjob_cache' => array( 'name' => 'ForceCronjobCache',
                                                                         'datatype' => 'integer',
                                                                         'default' => 1,
                                                                         'required' => true ),
                                         'object_count' => array( 'name' => 'ObjectCount',
                                                                  'datatype' => 'integer',
                                                                  'default' => 1,
                                                                  'required' => true ),
                                         'name' => array( 'name' => 'Name',
                                                          'datatype' => 'string',
                                                          'default' => '',
                                                          'required' => true ),
                                         'identifier' => array( 'name' => 'Identifier',
                                                                'datatype' => 'string',
                                                                'default' => '',
                                                                'required' => true ),
                                         'private_comment' => array( 'name' => 'PrivateComment',
                                                                     'datatype' => 'string',
                                                                     'default' => '',
                                                                     'required' => true ),
                                         'public_comment' => array( 'name' => 'PublicComment',
                                                                    'datatype' => 'string',
                                                                    'default' => '',
                                                                    'required' => true ) ),
                      'keys' => array( 'id', 'status' ),
                      'function_attributes' => array( 'source_list' => 'sourceList',
                                                      'draft_source_list' => 'draftSourceList',
                                                      'can_remove' => 'canRemove',
                                                      'can_create' => 'canCreate',
                                                      'can_edit' => 'canEdit' ),
                      'increment_key' => 'id',
                      'class_name' => 'eZSyndicationFeed',
                      'sort' => array( 'name' => 'asc' ),
                      'name' => 'ezsyndication_feed' );
    }

    /*!
     Add new Source to syndication feed

     \param Node ID
     \param Feed type

     \return new Feed Source
    */
    function addSource( $nodeID, $sourceType )
    {
        $sourceFeed = eZSyndicationFeedSource::create( $this->attribute( 'id' ),
                                                       $nodeID,
                                                       $sourceType );
        $sourceFeed->store();
        return $sourceFeed;
    }

    /*!
     Fetch feed sources

     \return list of feed sources
    */
    function &draftSourceList()
    {
        $publishList = eZSyndicationFeedSource::fetchList( $this->attribute( 'id' ),
                                                           eZSyndicationFeed::STATUS_PUBLISHED );
        $draftList = eZSyndicationFeedSource::fetchList( $this->attribute( 'id' ),
                                                         eZSyndicationFeed::STATUS_DRAFT );
        $draftIDList = array();
        foreach( $draftList as $draft )
        {
            $draftIDList[] = $draft->attribute( 'id' );
        }
        foreach( $publishList as $published )
        {
            if ( !in_array( $published->attribute( 'id' ), $draftIDList ) )
            {
                $draftList[] = eZSyndicationFeedSource::fetchDraft( $published->attribute( 'id' ) );
            }
        }
        return $draftList;
    }

    /*!
     \static

     Fetch source by object

     \param eZContentObject

     \return eZSyndicationFeedSource
    */
    function fetchSourceByObject( $contentObject )
    {
        $sourceNodeArray = array();
        foreach( $this->attribute( 'source_list' ) as $source )
        {
            $sourceNodeArray[$source->attribute( 'node_id' )] = $source;
        }

        foreach( $contentObject->attribute( 'assigned_nodes' ) as $node )
        {
            foreach( $sourceNodeArray as $nodeID => $source )
            {
                if ( strpos( $node->attribute( 'path_string' ), '/' . $nodeID . '/' ) !== false )
                {
                    return $source;
                }
            }
        }

        return false;
    }

    /*!
     Fetch feed sources

     \return list of feed sources
    */
    function &sourceList( $status = false )
    {
        if ( $status === false )
        {
            $status = $this->attribute( 'status' );
        }
        $retVal = eZSyndicationFeedSource::fetchList( $this->attribute( 'id' ),
                                                      $status );
        return $retVal;
    }

    /*!
     \static
     Fetch syndication feed

     \param feed id
     \param status, default published
    */
    static function fetch( $id,
                    $status = eZSyndicationFeed::STATUS_PUBLISHED,
                    $asObject = true )
    {
        $condArray = array( 'id' => $id );
        if ( $status !== false )
        {
            $condArray['status'] = $status;
        }
        return eZPersistentObject::fetchObject( eZSyndicationFeed::definition(),
                                                null,
                                                $condArray,
                                                $asObject );
    }

    /*!
      \static
      Fetch draft of eZSyndicationFeed object. A new object is created if none exist.
     */
    static function fetchDraft( $id, $asObject = true )
    {
        $draft = eZSyndicationFeed::fetch( $id, eZSyndicationFeed::STATUS_DRAFT, $asObject );
        if ( !$draft )
        {
            $draft = eZSyndicationFeed::fetch( $id, eZSyndicationFeed::STATUS_PUBLISHED, $asObject );
            if ( $draft )
            {
                $draft->setAttribute( 'status', eZSyndicationFeed::STATUS_DRAFT );
                $draft->store();
            }
        }

        if ( !$draft )
        {
            $draft = eZSyndicationFeed::create();
        }
        return $draft;
    }

    /*!
     \static
     Fetch syndication feed by identifier

     \param feed identifier
    */
    static function fetchByIdentifier( $identifier,
                                $status = eZSyndicationFeed::STATUS_PUBLISHED,
                                $asObject = true )
    {
        $condArray = array( 'identifier' => $identifier,
                            'status' => eZSyndicationFeed::STATUS_PUBLISHED );
        return eZPersistentObject::fetchObjectList( eZSyndicationFeed::definition(),
                                                    null,
                                                    $condArray,
                                                    $asObject );
    }

    /*!
     \static
     Fetch syndication list

     \param feed offset ( default 0 )
     \param number of syndocation feeds ( default 15 )
     \param status of syndication feed
    */
    static function fetchList( $offset = 0,
                        $limit = 15,
                        $status = eZSyndicationFeed::STATUS_PUBLISHED,
                        $asObject = true )
    {
        return eZPersistentObject::fetchObjectList( eZSyndicationFeed::definition(),
                                                    null,
                                                    array( 'status' => $status ),
                                                    null,
                                                    array( 'offset' => $offset,
                                                           'limit' => $limit ),
                                                    $asObject );
    }

    /*!
     Fetch Feed list description as XML document

     \return DOMDocument
    */
    function serializeSummary()
    {
        $dom = new DOMDocument( '1.0', 'utf-8' );
        $feedSummaryNode = $dom->createElement( 'feed_item' );
        $feedSummaryNode->setAttribute( 'feed_id', $this->attribute( 'id' ) );
        $feedSummaryNode->setAttribute( 'name', $this->attribute( 'name' ) );
        $feedSummaryNode->setAttribute( 'created_ts', $this->attribute( 'created_ts' ) );
        $feedSummaryNode->setAttribute( 'description', $this->attribute( 'public_comment' ) );
        $dom->appendChild( $feedSummaryNode );

        return $dom;
    }

    /*!
     Filename of list of remote ID's provided by this feed

     \param force fetch, optional, false by default. Used to override cahce.

     \return array of remote ID's
    */
    function feedList( $force = false )
    {
    }

    /*!
     \static

     Checks if user can remove feed.

     \return true or false
    */
    static function &canRemove()
    {
        $user = eZUser::instance();
        $accessArray = $user->hasAccessTo( 'syndication', 'remove_feed' );
        $result = $accessArray['accessWord'] == 'yes';
        return $result;
    }

    /*!
     \static

     Checks if user can create feed.

     \return true or false
    */
    static function &canCreate()
    {
        $user = eZUser::instance();
        $accessArray = $user->hasAccessTo( 'syndication', 'create_feed' );
        $result = $accessArray['accessWord'] == 'yes';
        return $result;
    }

    /*!
     \static

     Checks if user can edit feed.

     \return true or false
    */
    static function &canEdit()
    {
        $user = eZUser::instance();
        $accessArray = $user->hasAccessTo( 'syndication', 'edit_feed' );
        $result = $accessArray['accessWord'] == 'yes';
        return $result;
    }

    /*!
     \static
     Create new syndication feed

     \return Syndication feed
    */
    static function create()
    {
        $user = eZUser::instance();
        $row = array( 'creator_id' => $user->attribute( 'contentobject_id' ),
                      'created_ts' => time(),
                      'status' => eZSyndicationFeed::STATUS_DRAFT );
        return new eZSyndicationFeed( $row );
    }

    /*!
     \reimp
     Remove current or specified feed.

     \param feed id, optional, current object if none specified
    */
    function removeFeed( $ID = false )
    {
        if ( $ID !== false )
        {
            $feed = eZSyndicationFeed::fetch( $ID );
            if ( $feed )
            {
                $feed->removeFeed();
            }
            return;
        }

        foreach( $this->sourceList() as $source )
        {
            $source->removeSource();
        }

        eZPersistentObject::remove();
    }

    /*!
      Publish eZSyndicationFeed object.
      Sets the status to published, stores the object and removes the draft version.
    */
    function publish()
    {
        foreach( $this->sourceList( eZSyndicationFeed::STATUS_PUBLISHED ) as $source )
        {
            $source->removePublish();
        }

        foreach( $this->sourceList() as $source )
        {
            $source->publish();
        }

        $this->setAttribute( 'status', eZSyndicationFeed::STATUS_PUBLISHED );
        $this->store();

        $this->removeDraft();
    }

    /*!
     Remove draft.
    */
    function removeDraft()
    {
        $feedDraft = eZSyndicationFeed::fetchDraft( $this->attribute( 'id' ) );

        foreach( $feedDraft->sourceList() as $source )
        {
            $source->removeDraft();
        }

        $feedDraft->remove();
    }
}

?>
