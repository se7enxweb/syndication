<?php
//
// Definition of eZSyndicationFeedItemExport class
//
// Created on: <18-Sep-2006 15:47:09 hovik>
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

/*! \file ezsyndicationfeeditemexport.php
*/

/*!
  \class eZSyndicationFeedItemExport ezsyndicationfeeditemexport.php
  \brief The class eZSyndicationFeedItemExport does

*/

class eZSyndicationFeedItemExport extends eZPersistentObject
{
    /*!
     Constructor
    */
    function __construct($row = array() )
    {
        $this->eZPersistentObject( $row );
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
                      "function_attributes" => array( 'option_array' => 'optionArray' ),
                      "increment_key" => "id",
                      "class_name" => "eZSyndicationFeedItemExport",
                      "sort" => array( "id" => "desc" ),
                      "name" => "ezsyndication_feed_item_export" );
    }

    /*!
     Fetch object.

     \param feed item id
     \param asObject
    */
    static function fetch( $id, $asObject = true )
    {
        return eZSyndicationFeedItemExport::fetchObject( eZSyndicationFeedItemExport::definition(),
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
            case 'option_array':
            {
                $optionDef = $this->attribute( 'options' );
                $retVal = $optionDef == '' ? array() : unserialize( $optionDef );
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
     Create

     \param feedID
     \param eZContentObjectTreeNode
    */
    static function create( $feedID,
                     $contentNode )
    {
        $contentObject = $contentNode->attribute( 'object' );
        $feedItem = new eZSyndicationFeedItemExport( array( 'feed_id' => $feedID,
                                                            'depth' => $contentNode->attribute( 'depth' ),
                                                            'host_id' => eZSyndicationFeedItemExport::localHostID(),
                                                            'remote_id' => $contentObject->attribute( 'remote_id' ),
                                                            'contentobject_version' => $contentObject->attribute( 'current_version' ),
                                                            'modified' => time() ) );
        $feedItem->setNodeDetails( $contentNode );
        return $feedItem;
    }

    /*!
     \static
     Get unique host ID
    */
    static function localHostID()
    {
        $db = eZDB::instance();

        $resultSet = $db->arrayQuery( 'SELECT value FROM ezsite_data WHERE name=\'ezpublish_site_id\'' );

        if ( count( $resultSet ) == 1 )
        {
            return $resultSet[0]['value'];
        }

        $siteID = md5( time() . '-' . mt_rand() );
        $db->query( 'INSERT INTO ezsite_data ( name, value ) values( \'ezpublish_site_id\', \'' . $siteID . '\' )' );

        return $siteID;
    }

    /*!
     Set node feed details

     \param eZContentObjectTreeNode
    */
    function setNodeDetails( $contentNode )
    {
        $ini = eZINI::instance();
        $contentObject = $contentNode->attribute( 'object' );
        $currentVersion = $contentObject->attribute( 'current' );
        $translationList = $currentVersion->translationList( false, false );
        $url = $contentNode->attribute( 'url_alias' );
        $siteURL = $ini->variable( 'SiteSettings', 'SiteURL' );
        eZURI::transformURI( $url, false );
        $url = 'http://' . $siteURL . $url;
        $this->setOptionArray( array( 'object_remote_id' => $contentObject->attribute( 'remote_id' ),
                                      'published' => $contentObject->attribute( 'published' ),
                                      'modified' => $contentObject->attribute( 'modified' ),
                                      'version' => $contentObject->attribute( 'current_version' ),
                                      'name' => $contentObject->attribute( 'name' ),
                                      'original_url' => $url,
                                      'languages' => $translationList,
                                      'original_depth' => $contentNode->attribute( 'depth' ),
                                      'node_remote_id' => $contentNode->attribute( 'remote_id' ),
                                      'default_language' => $contentObject->attribute( 'default_language' ) ) );
    }

    /*!
     \static
     Fetch list based on feed ID and min modified TS
     Sorted so highest level is returned first.

     \param FeedID
     \param minimum modified ID ( optional )

     \return eZSyndicationFeedItem list
    */
    static function feedItemListByFeedID( $feedID,
                                   $modified = false,
                                   $asObject = true )
    {
        $condArray = array( 'feed_id' => $feedID );
        if ( $modified !== false )
        {
            $condArray['modified'] = array( '>', $modified );
        }
        return eZSyndicationFeedItemExport::fetchObjectList( eZSyndicationFeedItemExport::definition(),
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
    */
    static function maxModified( $feedID = false )
    {
        $condArray = array();
        if ( $feedID !== false )
        {
            $condArray['feed_id'] = $feedID;
        }
        $resultSet = eZSyndicationFeedItemExport::fetchObject( eZSyndicationFeedItemExport::definition(),
                                                               array(),
                                                               $condArray,
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
     Fetch object by contentobject ID

     \param FeedID
     \param eZContentObject

     \return eZSyndicationFeedItem object
    */
    static function fetchByContentObject( $feedID, $contentObject, $asObject = true )
    {
        return eZPersistentObject::fetchObject( eZSyndicationFeedItemExport::definition(),
                                                null,
                                                array( 'feed_id' => $feedID,
                                                       'remote_id' => $contentObject->attribute( 'remote_id' ) ),
                                                $asObject );
    }

    /*!
     \static
     Create new eZSyndicationFeedItem instance. If it allready exists, update existing

     \param FeedID
     \param eZContentObject
    */
    static function update( $feedID, $contentNode )
    {
        $contentObject = $contentNode->attribute( 'object' );
        $feedItem = eZSyndicationFeedItemExport::fetchByContentObject( $feedID, $contentObject );
        if ( !$feedItem )
        {
            $feedItem = eZSyndicationFeedItemExport::create( $feedID, $contentNode );
            $feedItem->store();
        }
        if ( $contentObject->attribute( 'current_version' ) != $feedItem->attribute( 'contentobject_version' ) )
        {
            $feedItem->setAttribute( 'contentobject_version', $contentObject->attribute( 'current_version' ) );
            $feedItem->setAttribute( 'depth', $contentNode->attribute( 'depth' ) );
            $feedItem->setNodeDetails( $contentNode );
            $feedItem->store();
        }
    }

    /*!
     \reimp
    */
    function store( $fieldFilters = null )
    {
        $this->setAttribute( 'modified', time() );
        eZPersistentObject::store( $fieldFilters );
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
}

?>
