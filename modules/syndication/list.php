<?php
//
// Definition of List class
//
// Created on: <12-Sep-2004 16:41 kk>
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

/*! \file list.php
*/

/*!
  \class List list.php
  \brief The class List does

*/

$module =& $Params["Module"];
$offset = $Params['Offset'];
$limit = 15;

$http = eZHTTPTool::instance();

if ( $http->hasPostVariable( 'CreateButton' ) &&
          eZSyndicationFeed::canCreate() )
{
    $feed = eZSyndicationFeed::create();
    $feed->store();

    return $module->redirectToView( 'edit', array( $feed->attribute( 'id' ) ) );
}
else if ( $http->hasPostVariable( 'RemoveButton' ) &&
          eZSyndicationFeed::canRemove() )
{
    foreach ( $http->postVariable( 'RemoveFeedIDArray' ) as $feedID )
    {
        eZSyndicationFeed::removeFeed( $feedID );
    }
}

$feedList = eZSyndicationFeed::fetchList( $offset, $limit );

include_once( "kernel/common/template.php" );
$tpl = templateInit();
$tpl->setVariable( 'feed_list', $feedList );

$Result = array();
$Result['content'] = $tpl->fetch( "design:syndication/list.tpl" );
$Result['path'] = array( array( 'url' => 'syndication/menu',
                                'text' => ezpI18n::tr( 'syndication/list', 'Menu' ) ),
                         array( 'url' => false,
                                'text' => ezpI18n::tr( 'syndication/list', 'List' ) ) );

?>
