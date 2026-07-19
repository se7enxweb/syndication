<?php
//
// Definition of eZSyndicationFeedSourceFilter class
//
// Created on: <12-Sep-2004 17:43:39 kk>
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

/*! \file ezsyndicationfeedsourcefilter.php
*/

/*!
  \class eZSyndicationFeedSourceFilter ezsyndicationfeedsourcefilter.php
  \brief The class eZSyndicationFeedSourceFilter does

*/

class eZSyndicationFeedSourceFilter extends eZPersistentObject
{
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
                                         'status' => array( 'name' => 'status',
                                                        'datatype' => 'integer',
                                                        'default' => 0,
                                                        'required' => true ),
                                         'feed_source_id' => array( 'name' => 'FeedSourceID',
                                                                    'datatype' => 'integer',
                                                                    'default' => 0,
                                                                    'required' => true ),
                                         'filter_id' => array( 'name' => 'FilterID',
                                                          'datatype' => 'string',
                                                          'default' => 0,
                                                          'required' => true ) ),
                      'keys' => array( 'id', 'status' ),
                      'function_attributes' => array( 'filter' => 'filter',
                                                      'feed' => 'feed' ),
                      'increment_key' => 'id',
                      'class_name' => 'eZSyndicationFeedSourceFilter',
                      'sort' => array( 'id' => 'asc' ),
                      'name' => 'ezsyndication_feed_source_filter' );
    }

    /*!
     \static
     Create new Filter.

     \param source id
     \param specified filter type

    */
    static function create( $sourceID,
                     $filterType )
    {
        $filter = eZSyndicationFilter::create( $filterType );
        $filter->store();

        $row = array( 'feed_source_id' => $sourceID,
                      'status' => eZSyndicationFeed::STATUS_DRAFT,
                      'filter_id' => $filter->attribute( 'id' ) );
        return new eZSyndicationFeedSourceFilter( $row );
    }

    /*!
     Get feed.

     \return feed.
    */
    function &feed()
    {
        $feedSource = eZSyndicationFeedSource::fetch( $this->attribute( 'feed_source_id' ),
                                                      $this->attribute( 'status' ) );
        $result = $feedSource->attribute( 'feed' );
        return $result;
    }

    /*!
     Filter specified object.

     \param Content Object

     \return true if object was accepted, false if not.
    */
    function filterObject( $contentObject )
    {
        $filter = $this->filter();
        return $filter->filter( $contentObject );
    }

    /*!
     Return filter object for this filter

     \return filter object.
    */
    function &filter()
    {
        switch( $this->attribute( 'status' ) )
        {
            case eZSyndicationFeed::STATUS_DRAFT:
            {
                $result = eZSyndicationFilter::fetchDraft( $this->attribute( 'filter_id' ) );
            } break;

            default:
            case eZSyndicationFeed::STATUS_PUBLISHED:
            {
                $result = eZSyndicationFilter::fetch( $this->attribute( 'filter_id' ) );
            } break;
        }
        return $result;
    }

    /*!
     \static
     Fetch feed source filter by ID.

     \param feed source filter ID
     \version number

     \return eZSyndicationFeedSourceFilter object
    */
    static function fetch( $filterID, $status = eZSyndicationFeed::STATUS_PUBLISHED )
    {
        return eZPersistentObject::fetchObject( eZSyndicationFeedSourceFilter::definition(),
                                                null,
                                                array( 'id' => $filterID,
                                                       'status' => $status ) );
    }

    /*!
     \static
     Fetch based on feed source id

     \param feed source id

     \return list of feed sources
    */
    static function fetchList( $feedSourceID,
                        $offset = 0,
                        $limit = 100,
                        $status = eZSyndicationFeed::STATUS_PUBLISHED,
                        $additionalConditions = array(),
                        $asObject = true )
    {
        $condArray = array( 'status' => $status,
                            'feed_source_id' => $feedSourceID );

        $condArray = array_merge( $condArray, $additionalConditions );

        return eZPersistentObject::fetchObjectList( eZSyndicationFeedSourceFilter::definition(),
                                                    null,
                                                    $condArray,
                                                    array( 'id' => 'desc' ),
                                                    array( 'limit' => $limit,
                                                           'offset' => $offset ),
                                                    $asObject );
    }

    /*!
     \static

     Fetch draft

     \param installation ID
    */
    static function fetchDraftList( $feedSourceID,
                             $asObject = true )
    {
        $draftList = eZSyndicationFeedSourceFilter::fetchList( $feedSourceID,
                                                               0,
                                                               100,
                                                               eZSyndicationFeed::STATUS_DRAFT );
        $publishList = eZSyndicationFeedSourceFilter::fetchList( $feedSourceID,
                                                                 0,
                                                                 100,
                                                                 eZSyndicationFeed::STATUS_PUBLISHED );
        $draftIDList = array();
        foreach( $draftList as $draft )
        {
            $draftIDList[] = $draft->attribute( 'id' );
        }

        // Create draft from published item, if draft does not exist. Ignore if draft already exists.
        foreach( $publishList as $published )
        {
            if ( !in_array( $published->attribute( 'id' ), $draftIDList ) )
            {
                $draftList[] = eZSyndicationFeedSourceFilter::fetchDraft( $published->attribute( 'id' ) );
            }
        }

        return $draftList;
    }

    /*!
     \reimp
     Remove all feed sources according to feed ID.

     \param Source feed id, optional, current object if none specified
    */
    function removeFilter( $ID = false )
    {
        if ( $ID !== false )
        {
            $filter = eZSyndicationFeedSourceFilter::fetch( $ID );
            if ( $filter )
            {
                $filter->removeFilter();
            }
            return;
        }

        eZPersistentObject::remove();
    }

    /*!
      \static
      Fetch draft of eZSyndicationFeed object. A new object is created if none exist.
     */
    static function fetchDraft( $id, $asObject = true )
    {
        $draft = eZSyndicationFeedSourceFilter::fetch( $id, eZSyndicationFeed::STATUS_DRAFT, $asObject );
        if ( !$draft )
        {
            $draft = eZSyndicationFeedSourceFilter::fetch( $id, eZSyndicationFeed::STATUS_PUBLISHED, $asObject );
            if ( $draft )
            {
                $draft->setAttribute( 'status', eZSyndicationFeed::STATUS_DRAFT );
                $draft->store();
            }
        }

        if ( !$draft )
        {
            $draft = eZSyndicationFeedSourceFilter::create();
        }
        return $draft;
    }

    /*!
      Publish eZSyndicationDraft object.
      Sets the status to published, stores the object and removes the draft version.
    */
    function publish()
    {
        $filter = $this->filter();
        $filter->publish();

        $this->setAttribute( 'status', eZSyndicationFeed::STATUS_PUBLISHED );
        $this->store();
    }

    /*!
     Remove published.
    */
    function removePublish()
    {
        $published = $this->fetch( $this->attribute( 'id' ), eZSyndicationFeed::STATUS_PUBLISHED );
        if ( $published )
        {
            $published->remove();
        }
    }

    /*!
     Remove draft.
    */
    function removeDraft()
    {
        $draft = $this->fetchDraft( $this->attribute( 'id' ) );
        $draft->remove();
    }
}

?>
