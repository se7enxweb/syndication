<?php
//
// Definition of eZSyndicationFeedItemStatus class
//
// Created on: <17-Sep-2006 16:37:24 hovik>
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

/*! \file ezsyndicationfeeditemstatus.php
*/

/*!
  \class eZSyndicationFeedItemStatus ezsyndicationfeeditemstatus.php
  \brief The class eZSyndicationFeedItemStatus does

*/

class eZSyndicationFeedItemStatus extends eZPersistentObject
{
    const STATUS_NONE = 0;
    const STATUS_PENDING = 1;
    const STATUS_INSTALLING = 2;
    const STATUS_INSTALLED = 3;
    const STATUS_FAILED = 4;
    const STATUS_DENIED = 5;
    const STATUS_DELETED = 6;
    const STATUS_IGNORED = 7;

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
                                         "feed_item_id" => array( 'name' => 'FeedID',
                                                                  'datatype' => 'integer',
                                                                  'default' => 0,
                                                                  'required' => true ),
                                         "modified" => array( 'name' => 'Modified',
                                                              'datatype' => 'integer',
                                                              'default' => 0,
                                                              'required' => true ),
                                         "created" => array( 'name' => 'Created',
                                                             'datatype' => 'integer',
                                                             'default' => 0,
                                                             'required' => true ),
                                         "published" => array( 'name' => 'Published',
                                                               'datatype' => 'integer',
                                                               'default' => 0,
                                                               'required' => true ),
                                         'options' => array( 'name' => 'Options',
                                                             'datatype' => 'string',
                                                             'default' => '',
                                                             'required' => true ),
                                         "status" => array( 'name' => 'Status',
                                                            'datatype' => 'integer',
                                                            'default' => 0,
                                                            'required' => true ) ),
                      "keys" => array( "id" ),
                      "function_attributes" => array( 'feed_item' => 'feedItem',
                                                      'option_array' => 'optionArray' ),
                      "increment_key" => "id",
                      "class_name" => "eZSyndicationFeedItemStatus",
                      "sort" => array( "id" => "asc" ),
                      "name" => "ezsyndication_feed_item_status" );
    }

    /*!
     \reimp
    */
    function attribute( $attr, $noFunction = false )
    {
        $retVal = null;
        switch( $attr )
        {
            case 'option_array':
            {
                $optionDef = $this->attribute( 'options' );
                $retVal = $optionDef == '' ? array() : unserialize( $optionDef );
            } break;

            case 'feed_item':
            {
                $retVal = eZSyndicationFeedItem::fetch( $this->attribute( 'feed_item_id' ) );
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

     Fetch list of eZSyndicationFeedItemStatus objects

     \param status
     \param offset
     \param limit
    */
    static function fetchList( $status,
                        $offset = 0,
                        $limit = 10,
                        $asObject = true )
    {
        return eZSyndicationFeedItemStatus::fetchObjectList( eZSyndicationFeedItemStatus::definition(),
                                                             null,
                                                             array( 'status' => $status ),
                                                             null,
                                                             array( 'offset' => $offset,
                                                                    'limit' => $limit ),
                                                             $asObject );
    }

    /*!
     \static

     Create new eZSyndicationFeedItemStatus object

     \param  eZSyndicationFeedItem ID

     \return new eZSyndicationFeedItem object
    */
    static function create( $feedItemID )
    {
        return new eZSyndicationFeedItemStatus( array( 'feed_item_id' => $feedItemID,
                                                       'created' => time(),
                                                       'status' => eZSyndicationFeedItemStatus::STATUS_NONE ) );
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
     \static

     Fetch eZSyndicationFeedItemStatus by ID
    */
    static function fetch( $id, $asObject = true )
    {
        return eZSyndicationFeedItemStatus::fetchObject( eZSyndicationFeedItemStatus::definition(),
                                                         null,
                                                         array( 'id' => $id ),
                                                         $asObject );
    }

    /*!
     \static

     Fetch object by eZSyndicationFeedItem ID
    */
    static function fetchByFeedItemID( $feedItemID,
                                $asObject = true )
    {
        return eZSyndicationFeedItemStatus::fetchObject( eZSyndicationFeedItemStatus::definition(),
                                                         null,
                                                         array( 'feed_item_id' => $feedItemID ),
                                                         $asObject );
    }

    /*!
     \static

     Get status name map
    */
    static function statusNameMap()
    {
        return array( eZSyndicationFeedItemStatus::STATUS_NONE => ezpI18n::tr( 'syndication', 'None' ),
                      eZSyndicationFeedItemStatus::STATUS_PENDING => ezpI18n::tr( 'syndication', 'Pending' ),
                      eZSyndicationFeedItemStatus::STATUS_INSTALLING => ezpI18n::tr( 'syndication', 'Installing' ),
                      eZSyndicationFeedItemStatus::STATUS_INSTALLED => ezpI18n::tr( 'syndication', 'Installed' ),
                      eZSyndicationFeedItemStatus::STATUS_FAILED => ezpI18n::tr( 'syndication', 'Failed' ),
                      eZSyndicationFeedItemStatus::STATUS_DENIED => ezpI18n::tr( 'syndication', 'Denied' ),
                      eZSyndicationFeedItemStatus::STATUS_DELETED => ezpI18n::tr( 'syndication', 'Deleted' ),
                      eZSyndicationFeedItemStatus::STATUS_IGNORED => ezpI18n::tr( 'syndication', 'Ignoring' ) );
    }

    /*!
     \static

     List of statuses which are allowed for users to set.
    */
    static function allowUserStatusList()
    {
        return array( eZSyndicationFeedItemStatus::STATUS_NONE,
                      eZSyndicationFeedItemStatus::STATUS_PENDING,
                      eZSyndicationFeedItemStatus::STATUS_DENIED );
    }

    /*!
     \static

     List of statuses which are allowed for users to set.
    */
    static function allowChangeFromStatusList()
    {
        return array( eZSyndicationFeedItemStatus::STATUS_NONE,
                      eZSyndicationFeedItemStatus::STATUS_FAILED,
                      eZSyndicationFeedItemStatus::STATUS_PENDING,
                      eZSyndicationFeedItemStatus::STATUS_IGNORED,
                      eZSyndicationFeedItemStatus::STATUS_INSTALLING,
                      eZSyndicationFeedItemStatus::STATUS_DENIED );
    }
}

?>
