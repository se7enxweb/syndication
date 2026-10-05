<?php
//
// Copyright (C) 1998 - 2026 7x & Exponential Foundation. All rights reserved.
// Copyright (C) 1999-2008 eZ Systems AS. All rights reserved.
//
// This file may be distributed and/or modified under the terms of the
// "GNU General Public License" version 2 (or any later version).
//

/*! \file pending_edit.php
    The items of an import and their import status: let the editor approve or deny what waits.
*/

$module = $Params['Module'];
if ( $redirect = eZSyndicationUI::redirectIfNotInstalled( $module ) )
{
    return $redirect;
}
$offset = isset( $Params['Offset'] ) ? max( 0, (int)$Params['Offset'] ) : 0;
$importID = (int)$Params['ImportID'];
$limit = 25;

$import = eZSyndicationImport::fetch( $importID );
if ( !$import )
{
    return $module->handleError( eZError::KERNEL_NOT_AVAILABLE, 'kernel' );
}

$http = eZHTTPTool::instance();

$allowChangeFromStatusList = eZSyndicationFeedItemStatus::allowChangeFromStatusList();
$allowChangeToStatusList = eZSyndicationFeedItemStatus::allowUserStatusList();

if ( $http->hasPostVariable( 'Update' ) )
{
    $changed = 0;
    $idList = $http->hasPostVariable( 'StatusIDList' ) ? (array)$http->postVariable( 'StatusIDList' ) : array();
    foreach ( $idList as $feedStatusID )
    {
        $feedItemStatus = eZSyndicationFeedItemStatus::fetch( (int)$feedStatusID );
        if ( !$feedItemStatus || !$http->hasPostVariable( 'StatusMode_' . (int)$feedStatusID ) )
        {
            continue;
        }
        $changeStatusTo = (int)$http->postVariable( 'StatusMode_' . (int)$feedStatusID );
        if ( in_array( (int)$feedItemStatus->attribute( 'status' ), $allowChangeFromStatusList, true ) &&
             in_array( $changeStatusTo, $allowChangeToStatusList, true ) &&
             (int)$feedItemStatus->attribute( 'status' ) !== $changeStatusTo )
        {
            $feedItemStatus->setAttribute( 'status', $changeStatusTo );
            $feedItemStatus->store();
            ++$changed;
        }
    }
    eZSyndicationUI::notice( 'feedback', ezpI18n::tr( 'extension/syndication', '%count items were changed.', null, array( '%count' => $changed ) ) );
    return $module->redirectToView( 'pending_edit', array( $importID ) );
}

$userParameters = isset( $Params['UserParameters'] ) && is_array( $Params['UserParameters'] ) ? $Params['UserParameters'] : array();
$statusNameMap = eZSyndicationFeedItemStatus::statusNameMap();

$statusFilter = -1;
if ( isset( $userParameters['statusFilter'] ) && isset( $statusNameMap[(int)$userParameters['statusFilter']] ) )
{
    $statusFilter = (int)$userParameters['statusFilter'];
}
$statusCondFilter = ( $statusFilter == -1 ) ? array_keys( $statusNameMap ) : $statusFilter;

$viewParameters = array( 'offset' => $offset, 'limit' => $limit );
if ( $statusFilter != -1 )
{
    $viewParameters['statusFilter'] = $statusFilter;
}

$feedStatusList = $import->fetchItemStatusList( $statusCondFilter, $offset, $limit );
$feedStatusListCount = $import->fetchItemStatusListCount( $statusCondFilter );

$tpl = eZTemplate::factory();
$tpl->setVariable( 'import', $import );
$tpl->setVariable( 'statusFilter', $statusFilter );
$tpl->setVariable( 'view_parameters', $viewParameters );
$tpl->setVariable( 'statusList', $feedStatusList );
$tpl->setVariable( 'statusListCount', $feedStatusListCount );
$tpl->setVariable( 'statusNameMap', $statusNameMap );
$tpl->setVariable( 'allowUserStatusList', $allowChangeToStatusList );
$tpl->setVariable( 'allowChangeFromStatusList', $allowChangeFromStatusList );
$tpl->setVariable( 'notices', eZSyndicationUI::takeNotices() );

$Result = array();
$Result['content'] = $tpl->fetch( 'design:syndication/pending_edit.tpl' );
$Result['path'] = array( array( 'url' => 'syndication/menu',
                                'text' => ezpI18n::tr( 'extension/syndication', 'Syndication' ) ),
                         array( 'url' => 'syndication/import_list',
                                'text' => ezpI18n::tr( 'extension/syndication', 'Imports' ) ),
                         array( 'url' => 'syndication/import_info/' . $importID,
                                'text' => $import->attribute( 'name' ) ),
                         array( 'url' => false,
                                'text' => ezpI18n::tr( 'extension/syndication', 'Items' ) ) );

?>
