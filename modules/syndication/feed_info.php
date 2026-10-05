<?php
//
// Copyright (C) 1998 - 2026 7x & Exponential Foundation. All rights reserved.
// Copyright (C) 1999-2008 eZ Systems AS. All rights reserved.
//
// This file may be distributed and/or modified under the terms of the
// "GNU General Public License" version 2 (or any later version).
//

/*! \file feed_info.php
    One export feed: its settings, sources and filters, the exported items, and "export now".
*/

$module = $Params['Module'];
if ( $redirect = eZSyndicationUI::redirectIfNotInstalled( $module ) )
{
    return $redirect;
}
$feedID = (int)$Params['FeedID'];
$http = eZHTTPTool::instance();
$user = eZUser::currentUser();

$feed = eZSyndicationFeed::fetch( $feedID );
if ( !$feed )
{
    return $module->handleError( eZError::KERNEL_NOT_AVAILABLE, 'kernel' );
}

$editAccess = $user->hasAccessTo( 'syndication', 'edit_export' );
$canEdit = $editAccess['accessWord'] != 'no';

if ( $http->hasPostVariable( 'ExportNowButton' ) && $canEdit )
{
    $error = '';
    $jobID = eZSyndicationJob::start( 'export', array( '--feed=' . $feedID ), $error );
    if ( !$jobID )
    {
        eZSyndicationUI::notice( 'error', ezpI18n::tr( 'extension/syndication', 'The run could not be started: %reason', null, array( '%reason' => $error ) ) );
        return $module->redirectToView( 'feed_info', array( $feedID ) );
    }
    return $module->redirectToView( 'feed_info', array( $feedID ), array(), array( 'job' => $jobID ) );
}

$limit = 25;
$offset = isset( $Params['Offset'] ) ? max( 0, (int)$Params['Offset'] ) : 0;
$db = eZDB::instance();
$countRow = $db->arrayQuery( "SELECT COUNT(*) AS c FROM ezsyndication_feed_item_export WHERE feed_id = " . (int)$feedID );
$itemCount = $countRow ? (int)$countRow[0]['c'] : 0;
$rows = $db->arrayQuery( "SELECT id, remote_id, depth, contentobject_version, modified FROM ezsyndication_feed_item_export WHERE feed_id = " . (int)$feedID . " ORDER BY modified DESC, id DESC",
                         array( 'offset' => $offset, 'limit' => $limit ) );
$items = array();
foreach ( (array)$rows as $row )
{
    $object = eZContentObject::fetchByRemoteID( $row['remote_id'] );
    $row['name'] = $object ? $object->attribute( 'name' ) : '';
    $row['node_url'] = ( $object && $object->attribute( 'main_node_id' ) ) ? 'content/view/full/' . $object->attribute( 'main_node_id' ) : false;
    $items[] = $row;
}

$sources = array();
foreach ( $feed->sourceList() as $source )
{
    $filters = array();
    foreach ( (array)$source->attribute( 'filter_list' ) as $sourceFilter )
    {
        $filter = $sourceFilter->filter();
        if ( $filter )
        {
            $filters[] = array( 'name' => $filter->attribute( 'type' ), 'limitation' => $filter->limitationText() );
        }
    }
    $sources[] = array( 'source' => $source, 'node' => $source->attribute( 'node' ), 'filters' => $filters );
}

$tpl = eZTemplate::factory();
$tpl->setVariable( 'feed', $feed );
$tpl->setVariable( 'sources', $sources );
$tpl->setVariable( 'export_items', $items );
$tpl->setVariable( 'item_count', $itemCount );
$tpl->setVariable( 'limit', $limit );
$tpl->setVariable( 'view_parameters', array( 'offset' => $offset ) );
$tpl->setVariable( 'can_edit', $canEdit );
$tpl->setVariable( 'soap_url', eZSys::serverURL() . eZSys::wwwDir() . '/soap.php' );
$tpl->setVariable( 'soap_enabled', eZINI::instance( 'soap.ini' )->variable( 'GeneralSettings', 'EnableSOAP' ) == 'true' );
$tpl->setVariable( 'last_run', eZSyndicationRunner::lastRun( eZSyndicationRunner::LAST_EXPORT ) );
$tpl->setVariable( 'notices', eZSyndicationUI::takeNotices() );
$userParameters = isset( $Params['UserParameters'] ) ? $Params['UserParameters'] : array();
$tpl->setVariable( 'job_id', isset( $userParameters['job'] ) && eZSyndicationJob::isID( $userParameters['job'] ) ? $userParameters['job'] : '' );

$Result = array();
$Result['content'] = $tpl->fetch( 'design:syndication/feed_info.tpl' );
$Result['path'] = array( array( 'url' => 'syndication/menu',
                                'text' => ezpI18n::tr( 'extension/syndication', 'Syndication' ) ),
                         array( 'url' => 'syndication/list',
                                'text' => ezpI18n::tr( 'extension/syndication', 'Feeds' ) ),
                         array( 'url' => false,
                                'text' => $feed->attribute( 'name' ) ) );

?>
