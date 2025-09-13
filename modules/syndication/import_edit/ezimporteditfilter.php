<?php
//
// Definition of eZImportEditFilter class
//
// Created on: <15-Nov-2004 06:32:12 hovik>
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

/*! \file ezimporteditfilter.php
*/

/*!
  \class eZImportEditFilter ezimporteditfilter.php
  \brief The class eZImportEditFilter does

*/

class eZImportEditFilter extends eZImportEditWizard
{
    /*!
     Constructor
    */
    function __construct( &$tpl, &$module, $storageName = false )
    {
        parent::__construct( $tpl, $module, $storageName );
    }

    /*!
     \reimp
    */
    function process()
    {
        $syndicationINI = eZINI::instance( 'syndication.ini' );
        foreach( $syndicationINI->variable( 'SyndicationFilters', 'FilterArray' ) as $filterType )
        {
            $filterClassName = 'eZFilter' . $filterType;
            $this->FilterArray[] = array( 'type' => $filterType,
                                          'name' => eval( 'return ' . $filterClassName . '::name();' ) );
        }

        return eZImportEditWizard::process();
    }

    /*!
     \reimp
    */
    function postCheck()
    {
        if ( $this->HTTP->hasPostVariable( 'AddFilterButton' ) )
        {
            $syndicationImport = $this->attribute( 'syndication_import' );
            $filter = eZSyndicationImportFilter::create( $syndicationImport->attribute( 'id' ),
                                                         $this->HTTP->postVariable( 'FilterType' ) );
            $filter->store();

            $this->Module->redirectTo( '/syndication/edit_import_filter/' . $filter->attribute( 'id' ) );
        }
        else if ( $this->HTTP->hasPostVariable( 'RemoveFilterIDArray' ) )
        {
            $syndicationImport = $this->attribute( 'syndication_import' );
            foreach( $this->HTTP->postVariable( 'RemoveFilterIDArray' ) as $removeID )
            {
                eZSyndicationImportFilter::removeFilter( $removeID );
            }
        }
        return eZImportEditWizard::postCheck();
    }

    /*!
     \reimp
    */
    function attributes()
    {
        return array_merge( eZImportEditWizard::attributes(), array( 'filter_array' ) );
    }

    /*!
     \reimp
    */
    function attribute( $attr )
    {
        switch( $attr )
        {
            case 'filter_array':
            {
                $result = $this->FilterArray;
            } break;

            default:
            {
                $result = eZImportEditWizard::attribute( $attr );
            } break;
        }

        return $result;
    }

    var $FilterArray = array();
}

?>
