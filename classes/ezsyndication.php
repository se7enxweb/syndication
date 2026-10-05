<?php
//
// Definition of eZSyndication class
//
// Created on: <11-Oct-2004 11:18:24 hovik>
//
// Copyright (C) 1998 - 2026 7x & Exponential Foundation. All rights reserved.
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

/*! \file ezsyndication.php
*/

/*!
  \class eZSyndication ezsyndication.php
  \brief The class eZSyndication does

*/

class eZSyndication
{
    const VERSION_MAJOR = 0;
    const VERSION_MINOR = 1;

    /*!
     Constructor
    */
    function __construct()
    {
    }

    /*!
     \static
     Get Syndication version

     \return Syndication version info
    */
    /*!
     \static
     Unserialize an option array stored in the database or received from a remote server. Objects are never
     created from it.

     \return array, empty array when the data is empty or not a serialized array
    */
    static function unserializeArray( $data )
    {
        if ( !is_string( $data ) || $data === '' )
        {
            return array();
        }
        $result = @unserialize( $data, array( 'allowed_classes' => false ) );
        return is_array( $result ) ? $result : array();
    }

    static function version()
    {
        return eZSyndication::VERSION_MAJOR . '.' . eZSyndication::VERSION_MINOR;
    }
}

?>
