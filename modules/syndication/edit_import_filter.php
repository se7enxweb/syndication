<?php
//
// Created on: <13-Oct-2004 00:36:05 hovik>
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

/*! \file edit_import_filter.php
*/

/*!
  \brief The class Edit_import_filter does

*/

$module = $Params['Module'];
$importFilterID = $Params['ImportFilterID'];
$http = eZHTTPTool::instance();

$importFilter = eZSyndicationImportFilter::fetch( $importFilterID );
$filter = $importFilter->filter();

if ( $http->hasPostVariable( 'Store' ) )
{
    if ( $filter->handleHTTPPost( $http ) )
    {
        $filter->store();
        return $module->redirectToView( 'import_edit');
    }
}
else if( $http->hasPostVariable( 'Cancel' ) )
{
    return $module->redirectToView( 'import_edit');
}

include_once( 'kernel/common/template.php' );
$tpl = templateInit();
$filter->setEditTPLVariables( $tpl );
$tpl->setVariable( 'import_filter', $importFilter );
$tpl->setVariable( 'filter', $filter );

$Result = array();
$Result['content'] = $tpl->fetch( 'design:syndication/edit_import_filter.tpl' );
$Result['path'] = array( array( 'url' => false,
                                'text' => ezpI18n::tr( 'syndication/list', 'Syndication Import' ) ),
                         array( 'url' => false,
                                'text' => ezpI18n::tr( 'syndication/list', 'Edit' ) ),
                         array( 'url' => false,
                                'text' => ezpI18n::tr( 'syndication/list', 'Edit Filter' ) ) );


?>
