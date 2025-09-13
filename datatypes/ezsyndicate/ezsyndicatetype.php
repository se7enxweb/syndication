<?php
//
// Definition of eZSyndicateType class
//
// Created on: <12-Sep-2004 21:05:39 kk>
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

/*! \file ezsyndicate.php
*/

/*!
  \class eZSyndicateType ezsyndicatetype.php
  \brief The class eZSyndicateType is a datatype prepping objects for syndication.

*/

class eZSyndicateType extends eZBooleanType
{

    const WORKFLOW_TYPE_STRING = 'ezsyndicatetype';
    /*!
     Constructor
    */
    function __construct()
    {
        $this->eZDataType( self::WORKFLOW_TYPE_STRING, ezpI18n::tr( 'kernel/classes/datatypes', 'Syndicate', 'Datatype name' ),
                           array( 'serialize_supported' => true,
                                  'object_serialize_map' => array( 'data_int' => 'enabled' ) ) );
    }

    /*!
     \reimp
    */
    function onPublish( &$contentObjectAttribute, &$contentObject, &$publishedNodes )
    {
        $db =& eZDB::instance();
        $db->query( 'INSERT INTO ezpending_actions( action, param ) VALUES ( "syndicate", '. (int)$contentObject->attribute( 'id' ) .' )' );
    }
}

eZDataType::register( eZSyndicateType::WORKFLOW_TYPE_STRING, 'ezsyndicatetype' );

?>