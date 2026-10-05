<?php
//
// Copyright (C) 1998 - 2026 7x & Exponential Foundation. All rights reserved.
// Copyright (C) 1999-2008 eZ Systems AS. All rights reserved.
//
// This file may be distributed and/or modified under the terms of the
// "GNU General Public License" version 2 (or any later version).
//

/*! \file list.php
    The export feeds: filter, sort, page, create, remove (with a confirmation step).
*/

$module = $Params['Module'];
if ( $redirect = eZSyndicationUI::redirectIfNotInstalled( $module ) )
{
    return $redirect;
}
$http = eZHTTPTool::instance();
$vp = eZSyndicationUI::listParameters( $Params, array( 'name', 'id', 'enabled' ) );
$confirmList = array();

if ( $http->hasPostVariable( 'CreateButton' ) && eZSyndicationFeed::canCreate() )
{
    $feed = eZSyndicationFeed::create();
    $feed->store();

    return $module->redirectToView( 'edit', array( $feed->attribute( 'id' ) ) );
}
else if ( $http->hasPostVariable( 'FilterButton' ) )
{
    $vp['q'] = trim( (string)$http->postVariable( 'Filter' ) );
    $vp['offset'] = 0;
    return $module->redirectTo( eZSyndicationUI::listURL( 'list', $vp ) );
}
else if ( $http->hasPostVariable( 'RemoveButton' ) && eZSyndicationFeed::canRemove() )
{
    // Step one: show what would go, remove nothing.
    $ids = $http->hasPostVariable( 'RemoveFeedIDArray' ) ? (array)$http->postVariable( 'RemoveFeedIDArray' ) : array();
    foreach ( $ids as $feedID )
    {
        $feed = eZSyndicationFeed::fetch( (int)$feedID );
        if ( $feed )
        {
            $confirmList[] = $feed;
        }
    }
    if ( !$confirmList )
    {
        eZSyndicationUI::notice( 'warning', ezpI18n::tr( 'extension/syndication', 'Select at least one feed to remove.' ) );
        return $module->redirectTo( eZSyndicationUI::listURL( 'list', $vp ) );
    }
}
else if ( $http->hasPostVariable( 'ConfirmRemoveButton' ) && eZSyndicationFeed::canRemove() )
{
    $removed = 0;
    $ids = $http->hasPostVariable( 'RemoveFeedIDArray' ) ? (array)$http->postVariable( 'RemoveFeedIDArray' ) : array();
    foreach ( $ids as $feedID )
    {
        if ( eZSyndicationFeed::removeFeed( (int)$feedID ) )
        {
            ++$removed;
        }
    }
    eZSyndicationUI::notice( 'feedback', ezpI18n::tr( 'extension/syndication', '%count feeds were removed.', null, array( '%count' => $removed ) ) );
    return $module->redirectTo( eZSyndicationUI::listURL( 'list', $vp, array( 'offset' => 0 ) ) );
}

$all = eZPersistentObject::fetchObjectList( eZSyndicationFeed::definition(), null,
                                            array( 'status' => eZSyndicationFeed::STATUS_PUBLISHED ) );
$total = 0;
$feedList = eZSyndicationUI::page( $all, $vp, array( 'name', 'identifier', 'public_comment' ), $total );

$tpl = eZTemplate::factory();
$tpl->setVariable( 'feed_list', $feedList );
$tpl->setVariable( 'total', $total );
$tpl->setVariable( 'all_count', is_array( $all ) ? count( $all ) : 0 );
$tpl->setVariable( 'vp', $vp );
$tpl->setVariable( 'view_parameters', array( 'offset' => $vp['offset'], 'q' => rawurlencode( $vp['q'] ), 'sort' => $vp['sort'], 'order' => $vp['order'] ) );
$tpl->setVariable( 'confirm_list', $confirmList );
$tpl->setVariable( 'notices', eZSyndicationUI::takeNotices() );
$tpl->setVariable( 'can_create', eZSyndicationFeed::canCreate() );
$tpl->setVariable( 'can_remove', eZSyndicationFeed::canRemove() );
$tpl->setVariable( 'can_edit', eZSyndicationFeed::canEdit() );

$Result = array();
$Result['content'] = $tpl->fetch( 'design:syndication/list.tpl' );
$Result['path'] = array( array( 'url' => 'syndication/menu',
                                'text' => ezpI18n::tr( 'extension/syndication', 'Syndication' ) ),
                         array( 'url' => false,
                                'text' => ezpI18n::tr( 'extension/syndication', 'Feeds' ) ) );

?>
