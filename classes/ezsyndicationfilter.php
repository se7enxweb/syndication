<?php
//
// Definition of eZSyndicationFilter class
//
// Created on: <30-Sep-2004 15:55:28 kk>
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

/*! \file ezsyndicationfilter.php
*/

/*!
  \class eZSyndicationFilter ezsyndicationfilter.php
  \brief The class eZSyndicationFilter is an abstract frame class for filtering content objects

*/

class eZSyndicationFilter extends eZPersistentObject
{
    /*!
     Constructor
    */
    function __construct( $row )
    {
        parent::__construct( $row );
        $this->Row = $row;
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
                                         'data_int_1' => array( 'name' => 'DataInt1',
                                                                'datatype' => 'integer',
                                                                'default' => 0,
                                                                'required' => true ),
                                         'data_int_2' => array( 'name' => 'DataInt2',
                                                                'datatype' => 'integer',
                                                                'default' => 0,
                                                                'required' => true ),
                                         'data_int_3' => array( 'name' => 'DataInt3',
                                                                'datatype' => 'integer',
                                                                'default' => 0,
                                                                'required' => true ),
                                         'type' => array( 'name' => 'Type',
                                                          'datatype' => 'string',
                                                          'default' => '',
                                                          'required' => true ),
                                         'class_name' => array( 'name' => 'ClassName',
                                                                'datatype' => 'string',
                                                                'default' => '',
                                                                'required' => true ),
                                         'data_text_1' => array( 'name' => 'DataText1',
                                                                 'datatype' => 'string',
                                                                 'default' => '',
                                                                 'required' => true ),
                                         'data_text_2' => array( 'name' => 'DataText2',
                                                                 'datatype' => 'string',
                                                                 'default' => '',
                                                                 'required' => true ),
                                         'data_text_3' => array( 'name' => 'DataText3',
                                                                 'datatype' => 'string',
                                                                 'default' => '',
                                                                 'required' => true ) ),
                      'keys' => array( 'id', 'status' ),
                      'function_attributes' => array( 'limitation_text' => 'limitationText',
                                                      'edit_template' => 'editTemplate',
                                                      'view_template' => 'viewTemplate' ),
                      'increment_key' => 'id',
                      'class_name' => 'eZSyndicationFilter',
                      'sort' => array( 'id' => 'asc' ),
                      'name' => 'ezsyndication_filter' );
    }

    /*!
     \static
     Create specified Filter

     \param filter type
    */
    static function create( $type )
    {
        return eval( 'return new eZFilter' . $type . '( array( "status" => ' . eZSyndicationFeed::STATUS_DRAFT . ' ) );' );
    }

    /*!
     Fetch filter by filter ID

     \param FilterID
     \param status ( optional )

     \return new Filter Object
    */
    static function fetch( $filterID, $status = eZSyndicationFeed::STATUS_PUBLISHED )
    {
        $syndicationFilter = eZPersistentObject::fetchObject( eZSyndicationFilter::definition(),
                                                              null,
                                                              array( 'id' => $filterID,
                                                                     'status' => $status ) );
        if ( !$syndicationFilter )
        {
            return false;
        }
        return new $syndicationFilter->Row['class_name']( $syndicationFilter->Row );
    }

    /*!
     \pure
     Filtering function. If specified input object passes filter criterias, true is returned, else false is returned.

     \param eZContentObject

     \param true if filter accepts content object. False if not.
    */
    function filter( $contentObject )
    {
        return false;
    }

    /*!
     \pure
     Handle HTTP post variabled from editing.

     \param HTTP object

     \return True if post variables accepted, false if need to be done once more.
    */
    function handleHTTPPost( &$http )
    {
        return true;
    }

    /*!
     Get template base

     \return base directory for templates.
    */
    function templateDirectory()
    {
        return 'design:syndication/filter';
    }

    /*!
     \pure
     Get edit template filename, templateDirectory prepended.

     \return complete template path.
    */
    function editTemplate()
    {
    }

    /*!
     \static
     \pure
     Return filter name
    */
    static function name()
    {
        return '';
    }

    /*!
     \pure
     Get view template filename, templateDirectory prepended.

     \return complete template path.
    */
    function viewTemplate()
    {
    }

    /*!
     \pure
     Set template edit variables.

     \param template resource
    */
    function setEditTPLVariables( &$tpl )
    {
    }

    /*!
     \pure
     Set template edit variables.

     \param template resource
    */
    function setViewTPLVariables( &$tpl )
    {
    }

    /*!
     \pure
     Get limitation text

     \return short text describing filter limitations.
    */
    function limitationText()
    {
    }

    /*!
     \private
     Copy existing object into new one.

     \param Array of override values
    */
    function copy( $rows = false )
    {
        if ( $rows === false )
        {
            $rows = array();
        }

        $def = $this->definition();
        $fields = $def['fields'];
        $className = $def['class_name'];

        foreach( array_keys( $fields ) as $key )
        {
            if ( !isset( $rows[$key] ) )
            {
                $rows[$key] = $this->attribute( $key );
            }
        }

        return new $className( $rows );
    }

    /*!
      \static
      Fetch draft of eZSyndicationFilter object. A new object is created if none exist.
    */
    static function fetchDraft( $id )
    {
        $draft = eZSyndicationFilter::fetch( $id, eZSyndicationFeed::STATUS_DRAFT );
        if ( !$draft )
        {
            $draft = eZSyndicationFilter::fetch( $id, eZSyndicationFeed::STATUS_PUBLISHED );
            if ( $draft )
            {
                $draft->setAttribute( 'status', eZSyndicationFeed::STATUS_DRAFT );
                $draft->store();
            }
        }

        if ( !$draft )
        {
            $draft = false;
        }
        return $draft;
    }

    /*!
      Publish eZSyndicationDraft object.
      Sets the status to published, stores the object and removes the draft version.
    */
    function publish()
    {
        $this->setAttribute( 'status', eZSyndicationFeed::STATUS_PUBLISHED );
        $this->store();

        $this->removeDraft();
    }

    /*!
     Remove draft.
    */
    function removeDraft()
    {
        $draft = $this->fetchDraft( $this->attribute( 'id' ) );
        $draft->remove();
    }

    /* Store row values to create subclass */
    var $Row;
}

?>
