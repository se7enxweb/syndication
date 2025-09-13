<?php
//
// Definition of eZFilterSection class
//
// Created on: <03-Oct-2004 13:51:40 hovik>
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

/*! \file ezfiltersection.php
*/

/*!
  \class eZFilterSection ezfiltersection.php
  \brief The class eZFilterSection does

*/

class eZFilterSection extends eZSyndicationFilter
{
    const DATA_FIELD = 'data_int_1';
    const NAME = 'Section';

    /*!
     Constructor
    */
    function __construct( $row )
    {
        $row['type'] = eZFilterSection::NAME;
        $row['class_name'] = 'eZFilterSection';
        $this->eZSyndicationFilter( $row );
    }

    /*!
     \reimp
    */
    function filter( $contentObject )
    {
        return ( $contentObject->attribute( 'section_id' ) == $this->attribute( eZFilterSection::DATA_FIELD ) );
    }

    /*!
     \reimp
    */
    function handleHTTPPost( &$http )
    {
        if ( $http->hasPostVariable( 'SectionID' ) )
        {
            $this->setAttribute( eZFilterSection::DATA_FIELD, $http->postVariable( 'SectionID' ) );
            return true;
        }
        return false;
    }

    /*!
     \reimp
    */
    function editTemplate()
    {
        $result = $this->templateDirectory() . '/edit_section.tpl';
        return $result;
    }

    /*!
     \reimp
    */
    function viewTemplate()
    {
        $result = $this->templateDirectory() . '/view_section.tpl';
        return  $result;
    }

    /*!
     \reimp
    */
    function setEditTPLVariables( &$tpl )
    {
        $tpl->setVariable( 'section_array', eZSection::fetchList() );
    }

    /*!
    \reimp
    */
    static function name()
    {
        return eZFilterSection::NAME;
    }

    /*!
     \reimp
    */
    function limitationText()
    {
        $result = '';
        $section = eZSection::fetch( $this->attribute( eZFilterSection::DATA_FIELD ) );
        if ( $section )
        {
            $result = $section->attribute( 'name' );
        }

        return $result;
    }
}

?>
