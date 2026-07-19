<?php
//
// Definition of SyndicationAction class
//
// Created on: <29-Sep-2004 16:53:23 kk>
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

/*! \file syndicationaction.php
*/

/*!
  \class SyndicationAction syndicationaction.php
  \brief The class SyndicationAction is a wrapper class for common operations related with syndication feeds.

*/

class SyndicationAction
{
    /*!
     Constructor
    */
    function __construct( $module )
    {
        $this->Module = $module;
        $this->HTTP = eZHTTPTool::instance();
    }

    /*!
     Store Import

     \param import object ( optional )
    */
    function storeImport( $import )
    {
        $http =& eZHttpTool::instance();

        switch( $this->Module['Step'] )
        {
            case 1:
            {
                $import->setAttribute( 'name', $http->postVariable( 'Name' ) );
                $import->setAttribute( 'server', $http->postVariable( 'Server' ) );
            } break;

            case 2:
            {
                //TODO - depricated
            }
        }
//        $import->setAttribute( 'comment', $this->Module->actionParameter( 'Comment' ) );
//        $import->setAttribute( 'enabled', ( $this->Module->actionParameter( 'Active' ) ? 1 : 0 ) );
        $import->sync();
    }

    var $HTTP;
    var $Module;
}

?>
