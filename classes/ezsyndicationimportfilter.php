<?php
//
// Definition of eZSyndicationImportFilter class
//
// Created on: <12-Oct-2004 23:37:47 hovik>
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

/*! \file ezsyndicationimportfilter.php
*/

/*!
  \class eZSyndicationImportFilter ezsyndicationimportfilter.php
  \brief The class eZSyndicationImportFilter does

*/

class eZSyndicationImportFilter extends eZPersistentObject
{
    /*!
     Constructor
    */
    function __construct($row )
    {
        $this->eZPersistentObject( $row );
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
                                         'import_id' => array( 'name' => 'ImportID',
                                                               'datatype' => 'integer',
                                                               'default' => 0,
                                                               'required' => true ),
                                         'filter_id' => array( 'name' => 'FilterID',
                                                               'datatype' => 'string',
                                                               'default' => 0,
                                                               'required' => true ) ),
                      'keys' => array( 'id', 'status' ),
                      'function_attributes' => array( 'filter' => 'filter',
                                                      'import' => 'import' ),
                      'increment_key' => 'id',
                      'class_name' => 'eZSyndicationImportFilter',
                      'sort' => array( 'id' => 'asc' ),
                      'name' => 'ezsyndication_import_filter' );
    }

    /*!
     \static
     Create new Filter.

     \param import id
     \param version
     \param specified filter type

    */
    static function create( $importID,
                     $filterType )
    {
        $filter = eZSyndicationFilter::create( $filterType );
        $filter->store();

        $row = array( 'import_id' => $importID,
                      'status' => eZSyndicationImport::STATUS_DRAFT,
                      'filter_id' => $filter->attribute( 'id' ) );
        return new eZSyndicationImportFilter( $row );
    }

    /*!
     Get import.

     \return import.
    */
    function import()
    {
        $result = eZSyndicationImport::fetch( $this->attribute( 'import_id' ),
                                              $this->attribute( 'status' ) );
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
    function filter()
    {
        $result = eZSyndicationFilter::fetch( $this->attribute( 'filter_id' ),
                                              $this->attribute( 'status' ) );
        return $result;
    }

    /*!
     Return filter object for this filter

     \return filter object.
    */
    function filterDraft()
    {
        $result = eZSyndicationFilter::fetchDraft( $this->attribute( 'filter_id' ) );
        return $result;
    }

    /*!
     \static
     Fetch import filter by ID.

     \param import filter ID

     \return eZSyndicationImportFilter object
    */
    static function fetch( $filterID )
    {
        return eZPersistentObject::fetchObject( eZSyndicationImportFilter::definition(),
                                                null,
                                                array( 'id' => $filterID ) );
    }

    /*!
     Remove filter.

     \param filter id
    */
    function removeFilter( $ID = false )
    {
        if ( $ID !== false )
        {
            $filter = eZSyndicationImportFilter::fetch( $ID );
            if ( $filter )
            {
                $filter->remove();
            }
            return;
        }

        eZPersistentObject::remove();
    }

    /*!
     \static
     Fetch based on import id

     \param import id

     \return list of import filters
    */
    static function fetchList( $importID, $status = eZSyndicationImport::STATUS_DRAFT )
    {
        return eZPersistentObject::fetchObjectList( eZSyndicationImportFilter::definition(),
                                                    null,
                                                    array( 'import_id' => $importID,
                                                           'status' => $status ) );
    }

    /*!
    */
    function publish()
    {
        $filter = $this->filterDraft();
        $filter->publish();

        $this->setAttribute( 'status', eZSyndicationImport::STATUS_PUBLISHED );
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

    /*!
      \static
      Fetch draft of eZSyndicationImportFilter object. A new object is created if none exist.
    */
    static function fetchDraft( $id )
    {
        $draft = eZSyndicationImportFilter::fetch( $id, eZSyndicationImport::STATUS_DRAFT );
        if ( !$draft )
        {
            $draft = eZSyndicationImportFilter::fetch( $id, eZSyndicationImport::STATUS_PUBLISHED );
            if ( $draft )
            {
                $draft->setAttribute( 'status', eZSyndicationImport::STATUS_DRAFT );
                $draft->store();
            }
        }

        if ( !$draft )
        {
            $draft = false;
        }
        return $draft;
    }

}

?>
