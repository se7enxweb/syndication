<?php
//
// Copyright (C) 1998 - 2026 7x & Exponential Foundation. All rights reserved.
// Copyright (C) 1999-2008 eZ Systems AS. All rights reserved.
//
// This file may be distributed and/or modified under the terms of the
// "GNU General Public License" version 2 (or any later version).
//

/*! \file edit.php
    Edit one export feed. The form works on a draft; Ok validates and publishes it, Cancel drops the draft.
*/

$module = $Params['Module'];
if ( $redirect = eZSyndicationUI::redirectIfNotInstalled( $module ) )
{
    return $redirect;
}
$feedID = (int)$Params['FeedID'];
$http = eZHTTPTool::instance();

if ( !eZSyndicationFeed::fetch( $feedID, false ) )
{
    eZSyndicationUI::notice( 'error', ezpI18n::tr( 'extension/syndication', 'There is no feed with the ID %id.', null, array( '%id' => $feedID ) ) );
    return $module->redirectToView( 'list' );
}

$syndicationFeed = eZSyndicationFeed::fetchDraft( $feedID );
$errors = array();

$postValue = function ( $name, $default = '' ) use ( $http )
{
    return $http->hasPostVariable( $name ) ? trim( (string)$http->postVariable( $name ) ) : $default;
};

if ( $http->hasPostVariable( 'Store' ) ||
     $http->hasPostVariable( 'AddSourceButton' ) ||
     $http->hasPostVariable( 'RemoveSourceButton' ) )
{
    $name = $postValue( 'Name', $syndicationFeed->attribute( 'name' ) );
    $identifier = $postValue( 'Identifier', $syndicationFeed->attribute( 'identifier' ) );
    $expiry = $postValue( 'ObjectExpiryTime', (string)$syndicationFeed->attribute( 'object_expiry_time' ) );
    $timeout = $postValue( 'CacheTimeout', (string)$syndicationFeed->attribute( 'cache_timeout' ) );

    if ( $name === '' )
    {
        $errors['Name'] = ezpI18n::tr( 'extension/syndication', 'Enter a name.' );
    }
    else if ( mb_strlen( $name ) > 255 )
    {
        $errors['Name'] = ezpI18n::tr( 'extension/syndication', 'The name may have at most 255 characters.' );
    }
    if ( $identifier !== '' )
    {
        if ( !preg_match( '/^[A-Za-z0-9_.\-]{1,255}$/', $identifier ) )
        {
            $errors['Identifier'] = ezpI18n::tr( 'extension/syndication', 'Use only letters, digits, dot, dash and underscore.' );
        }
        else
        {
            foreach ( (array)eZSyndicationFeed::fetchByIdentifier( $identifier ) as $other )
            {
                if ( (int)$other->attribute( 'id' ) !== $feedID )
                {
                    $errors['Identifier'] = ezpI18n::tr( 'extension/syndication', 'Another feed already uses this identifier.' );
                    break;
                }
            }
        }
    }
    if ( !ctype_digit( $expiry ) )
    {
        $errors['ObjectExpiryTime'] = ezpI18n::tr( 'extension/syndication', 'Enter a number of days, 0 or more.' );
    }
    if ( !ctype_digit( $timeout ) )
    {
        $errors['CacheTimeout'] = ezpI18n::tr( 'extension/syndication', 'Enter a number of minutes, 0 or more.' );
    }

    $syndicationFeed->setAttribute( 'name', $name );
    $syndicationFeed->setAttribute( 'identifier', $identifier );
    $syndicationFeed->setAttribute( 'public_comment', $postValue( 'PublicDescription' ) );
    $syndicationFeed->setAttribute( 'object_expiry_time', ctype_digit( $expiry ) ? (int)$expiry : 0 );
    $syndicationFeed->setAttribute( 'enabled', ( $http->hasPostVariable( 'Active' ) ? 1 : 0 ) );
    $syndicationFeed->setAttribute( 'force_cronjob_cache', ( $http->hasPostVariable( 'ForceCronjobCache' ) ? 1 : 0 ) );
    $syndicationFeed->setAttribute( 'cache_timeout', ctype_digit( $timeout ) ? (int)$timeout : 0 );
    $syndicationFeed->sync();
}

if ( $http->hasPostVariable( 'Store' ) && !$errors )
{
    $noSource = !count( $syndicationFeed->attribute( 'draft_source_list' ) );
    $syndicationFeed->publish();
    eZSyndicationUI::notice( 'feedback', ezpI18n::tr( 'extension/syndication', 'The feed "%name" was saved.', null, array( '%name' => $syndicationFeed->attribute( 'name' ) ) ) );
    if ( $noSource )
    {
        eZSyndicationUI::notice( 'warning', ezpI18n::tr( 'extension/syndication', 'The feed has no source yet, so nothing is exported.' ) );
    }
    return $module->redirectToView( 'list' );
}
else if ( $http->hasPostVariable( 'AddSourceButton' ) && !$errors )
{
    return $module->redirectToView( 'add_feed_source', array( $feedID ) );
}
else if ( $http->hasPostVariable( 'RemoveSourceButton' ) )
{
    $removeIDs = $http->hasPostVariable( 'RemoveSourceIDArray' ) ? (array)$http->postVariable( 'RemoveSourceIDArray' ) : array();
    foreach ( $removeIDs as $sourceID )
    {
        eZSyndicationFeedSource::removeSource( (int)$sourceID );
    }
    if ( !$removeIDs )
    {
        $errors['Sources'] = ezpI18n::tr( 'extension/syndication', 'Select at least one source to remove.' );
    }
}
else if ( $http->hasPostVariable( 'Cancel' ) )
{
    $syndicationFeed->removeDraft();
    return $module->redirectToView( 'list' );
}

$tpl = eZTemplate::factory();
$tpl->setVariable( 'syndication_feed', $syndicationFeed );
$tpl->setVariable( 'errors', $errors );
$tpl->setVariable( 'notices', eZSyndicationUI::takeNotices() );

$Result = array();
$Result['content'] = $tpl->fetch( 'design:syndication/edit.tpl' );
$Result['path'] = array( array( 'url' => 'syndication/menu',
                                'text' => ezpI18n::tr( 'extension/syndication', 'Syndication' ) ),
                         array( 'url' => 'syndication/list',
                                'text' => ezpI18n::tr( 'extension/syndication', 'Feeds' ) ),
                         array( 'url' => false,
                                'text' => ezpI18n::tr( 'extension/syndication', 'Edit' ) ) );

?>
