<?php
//
// Copyright (C) 1998 - 2026 7x & Exponential Foundation. All rights reserved.
// Copyright (C) 1999-2008 eZ Systems AS. All rights reserved.
//
// This file may be distributed and/or modified under the terms of the
// "GNU General Public License" version 2 (or any later version).
//

/*! \file import_info.php
    One import: its settings, the state of its items, "fetch now" and "import now".
*/

$module = $Params['Module'];
if ( $redirect = eZSyndicationUI::redirectIfNotInstalled( $module ) )
{
    return $redirect;
}
$importID = (int)$Params['ImportID'];
$http = eZHTTPTool::instance();
$user = eZUser::currentUser();

$import = eZSyndicationImport::fetch( $importID );
if ( !$import )
{
    return $module->handleError( eZError::KERNEL_NOT_AVAILABLE, 'kernel' );
}

$editAccess = $user->hasAccessTo( 'syndication', 'edit_import' );
$canEdit = $editAccess['accessWord'] != 'no';

if ( ( $http->hasPostVariable( 'FetchButton' ) || $http->hasPostVariable( 'ImportButton' ) ) && $canEdit )
{
    $fetch = $http->hasPostVariable( 'FetchButton' );
    if ( !$fetch && !$import->attribute( 'placement_node_id' ) )
    {
        eZSyndicationUI::notice( 'error', ezpI18n::tr( 'extension/syndication', 'This import has no location for the imported content. Edit the import and choose one.' ) );
        return $module->redirectToView( 'import_info', array( $importID ) );
    }
    $error = '';
    $jobID = eZSyndicationJob::start( 'import', array( '--import=' . $importID, $fetch ? '--fetch-only' : '--import-only' ), $error );
    if ( !$jobID )
    {
        eZSyndicationUI::notice( 'error', ezpI18n::tr( 'extension/syndication', 'The run could not be started: %reason', null, array( '%reason' => $error ) ) );
        return $module->redirectToView( 'import_info', array( $importID ) );
    }
    return $module->redirectToView( 'import_info', array( $importID ), array(), array( 'job' => $jobID ) );
}

$statusRows = array();
$limit = 15;
$offset = isset( $Params['Offset'] ) ? max( 0, (int)$Params['Offset'] ) : 0;
$db = eZDB::instance();
$feedID = (int)$import->attribute( 'feed_id' );
$countRow = $db->arrayQuery( "SELECT COUNT(*) AS c FROM ezsyndication_feed_item WHERE feed_id = $feedID" );
$itemCount = $countRow ? (int)$countRow[0]['c'] : 0;
$rows = $db->arrayQuery( "SELECT item.id AS id, item.remote_id AS remote_id, item.modified AS modified, status.status AS status
                          FROM ezsyndication_feed_item item LEFT JOIN ezsyndication_feed_item_status status ON item.id = status.feed_item_id
                          WHERE item.feed_id = $feedID ORDER BY item.modified DESC, item.id DESC",
                         array( 'offset' => $offset, 'limit' => $limit ) );
$names = eZSyndicationFeedItemStatus::statusNameMap();
foreach ( (array)$rows as $row )
{
    $row['status_name'] = isset( $names[(int)$row['status']] ) ? $names[(int)$row['status']] : '';
    $object = eZContentObject::fetchByRemoteID( $row['remote_id'] );
    $row['name'] = $object ? $object->attribute( 'name' ) : '';
    $statusRows[] = $row;
}

$tpl = eZTemplate::factory();
$tpl->setVariable( 'import', $import );
$tpl->setVariable( 'server', eZSyndicationUI::safeServer( $import ) );
$tpl->setVariable( 'has_login', (bool)( $import->option( 'login' ) !== false && $import->option( 'login' ) !== '' ) );
$tpl->setVariable( 'status_rows', $statusRows );
$tpl->setVariable( 'item_count', $itemCount );
$tpl->setVariable( 'limit', $limit );
$tpl->setVariable( 'view_parameters', array( 'offset' => $offset ) );
$tpl->setVariable( 'can_edit', $canEdit );
$tpl->setVariable( 'last_run', eZSyndicationRunner::lastRun( eZSyndicationRunner::LAST_IMPORT ) );
$tpl->setVariable( 'notices', eZSyndicationUI::takeNotices() );
$userParameters = isset( $Params['UserParameters'] ) ? $Params['UserParameters'] : array();
$tpl->setVariable( 'job_id', isset( $userParameters['job'] ) && eZSyndicationJob::isID( $userParameters['job'] ) ? $userParameters['job'] : '' );

$Result = array();
$Result['content'] = $tpl->fetch( 'design:syndication/import_info.tpl' );
$Result['path'] = array( array( 'url' => 'syndication/menu',
                                'text' => ezpI18n::tr( 'extension/syndication', 'Syndication' ) ),
                         array( 'url' => 'syndication/import_list',
                                'text' => ezpI18n::tr( 'extension/syndication', 'Imports' ) ),
                         array( 'url' => false,
                                'text' => $import->attribute( 'name' ) ) );

?>
