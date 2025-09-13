<?php
//
// Definition of eZFilterAttribute class
//
// Created on: <23-Sep-2006 15:16:50 hovik>
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

/*! \file ezfilterattribute.php
*/

/*!
  \class eZFilterAttribute ezfilterattribute.php
  \brief The class eZFilterAttribute does

*/

class eZFilterAttribute extends eZSyndicationFilter
{
    const DATA_FIELD = 'data_text_1';
    const NAME = 'Attribute';

    /*!
     Constructor
    */
    function __construct( $row )
    {
        $row['type'] = eZFilterAttribute::NAME;
        $row['class_name'] = 'eZFilterAttribute';
        $this->eZSyndicationFilter( $row );
    }

    /*!
     \reimp
    */
    function filter( $contentObject )
    {
        $dataMap = $contentObject->dataMap();

        return ( isset( $dataMap[$this->attribute( eZFilterAttribute::DATA_FIELD )] ) &&
                 $dataMap[$this->attribute( eZFilterAttribute::DATA_FIELD )]->content() );
    }

    /*!
     \reimp
    */
    function handleHTTPPost( &$http )
    {
        if ( $http->hasPostVariable( 'AttributeName' ) )
        {
            $this->setAttribute( eZFilterAttribute::DATA_FIELD, $http->postVariable( 'AttributeName' ) );
            return true;
        }
        return false;
    }

    /*!
     \reimp
    */
    function editTemplate()
    {
        $result = $this->templateDirectory() . '/edit_attribute.tpl';
        return $result;
    }

    /*!
     \reimp
    */
    function viewTemplate()
    {
        $result = $this->templateDirectory() . '/view_attribute.tpl';
        return  $result;
    }

    /*!
     \reimp
    */
    function setEditTPLVariables( &$tpl )
    {
        $tpl->setVariable( 'default_attribute', 'syndicate_object' );
    }

    /*!
    \reimp
    */
    static function name()
    {
        return eZFilterAttribute::NAME;
    }

    /*!
     \reimp
    */
    function limitationText()
    {
        $result = $this->attribute( eZFilterAttribute::DATA_FIELD );

        return $result;
    }
}

?>
