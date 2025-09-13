<?php
//
// Definition of Import_edit class
//
// Created on: <16-Nov-2004 23:33:49 hovik>
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

/*! \file import_edit.php
*/

/*!
  \class Import_edit import_edit.php
  \brief The class Import_edit does

*/

$module = $Params['Module'];

include_once( "kernel/common/template.php" );
$tpl = templateInit();

$wizardClass = eZImportEditWizard::instance( $tpl, $module, $Params );

if ( !$wizardClass )
{
    return $module->redirectTo( '/syndication/import_list' );
}

return $wizardClass->run();

?>
