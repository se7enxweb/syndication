<?php
//
// Created on: <17-Sep-2006 21:50:10 hovik>
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
// http://www.gnu.org/copyleft/gpl.html.//
// Contact licence@ez.no if any conditions of this licencing isn't clear to
// you.
//

/*! \file pending_edit.php
*/

/*!
  \brief The class Pending_Edit does

*/

$module =& $Params["Module"];
$offset = $Params['Offset'];
$importID = $Params['ImportID'];
$limit = 25;

$viewParameters = array( 'offset' => $offset ? $offset : '0',
                         'limit' => $limit );

$http = eZHttpTool::instance();

$allowChangeFromStatusList = eZSyndicationFeedItemStatus::allowChangeFromStatusList();
$allowChangeToStatusList = eZSyndicationFeedItemStatus::allowUserStatusList();

if ( $http->hasPostVariable( 'Update' ) )
{
    foreach( $http->postVariable( 'StatusIDList' ) as $feedStatusID )
    {
        $feedItemStatus = eZSyndicationFeedItemStatus::fetch( $feedStatusID );
        $changeStatusTo = $http->postVariable( 'StatusMode_' . $feedStatusID );
        if ( in_array( $feedItemStatus->attribute( 'status' ), $allowChangeFromStatusList ) &&
             in_array( $changeStatusTo, $allowChangeToStatusList ) )
        {
            $feedItemStatus->setAttribute( 'status', $changeStatusTo );
            $feedItemStatus->store();
        }
    }
}

if ( isset( $Params['UserParameters'] ) )
{
    $userParameters = $Params['UserParameters'];
}
else
{
    $userParameters = array();
}

$viewParameters = array_merge( $viewParameters, $userParameters );

$statusFilter = -1;
if ( isset( $userParameters['statusFilter'] ) &&
     in_array( $userParameters['statusFilter'],
               array_keys( eZSyndicationFeedItemStatus::statusNameMap() ) ) )
{
    $statusFilter = $userParameters['statusFilter'];
}
$statusCondFilter = ( $statusFilter == -1 ) ? array_keys( eZSyndicationFeedItemStatus::statusNameMap() ) :
    $statusFilter;

$import = eZSyndicationImport::fetch( $importID );
$feedStatusList = $import->fetchItemStatusList( $statusCondFilter,
                                                $offset,
                                                25 );
$feedStatusListCount = $import->fetchItemStatusListCount( $statusCondFilter );

include_once( "kernel/common/template.php" );
$tpl = templateInit();
$tpl->setVariable( 'import', $import );
$tpl->setVariable( 'statusFilter', $statusFilter );
$tpl->setVariable( 'view_parameters', $viewParameters );
$tpl->setVariable( 'statusList', $feedStatusList );
$tpl->setVariable( 'statusListCount', $feedStatusListCount );
$tpl->setVariable( 'statusNameMap', eZSyndicationFeedItemStatus::statusNameMap() );
$tpl->setVariable( 'allowUserStatusList', $allowChangeToStatusList );
$tpl->setVariable( 'allowChangeFromStatusList', $allowChangeFromStatusList );

$Result = array();
$Result['content'] = $tpl->fetch( "design:syndication/pending_edit.tpl" );
$Result['path'] = array( array( 'url' => 'syndication/import_list',
                                'text' => ezpI18n::tr( 'syndication/import', 'Syndication' ) ) );


?>
