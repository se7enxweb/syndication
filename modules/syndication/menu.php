<?php
//
// Copyright (C) 1998 - 2026 7x & Exponential Foundation. All rights reserved.
// Copyright (C) 1999-2008 eZ Systems AS. All rights reserved.
//
// This file may be distributed and/or modified under the terms of the
// "GNU General Public License" version 2 (or any later version).
//

/*! \file menu.php
    The start page of the module: what is exported, what is imported, the last runs and the problems found.
    Actions: create the tables, run every export or every import now.
*/

$module = $Params['Module'];
$http = eZHTTPTool::instance();
$user = eZUser::currentUser();

$canAccess = function ( $function ) use ( $user )
{
    $access = $user->hasAccessTo( 'syndication', $function );
    return $access['accessWord'] != 'no';
};
$canEditExport = $canAccess( 'edit_export' );
$canEditImport = $canAccess( 'edit_import' );

if ( $http->hasPostVariable( 'InstallTablesButton' ) )
{
    if ( $canEditExport && $canEditImport )
    {
        $messages = array();
        if ( eZSyndicationInstaller::install( null, $messages ) )
        {
            eZSyndicationUI::notice( 'feedback', ezpI18n::tr( 'extension/syndication', 'The tables were created.' ) );
        }
        else
        {
            eZSyndicationUI::notice( 'error', ezpI18n::tr( 'extension/syndication', 'The tables could not be created: %reason', null, array( '%reason' => implode( ' ', $messages ) ) ) );
        }
    }
    return $module->redirectToView( 'menu' );
}
else if ( ( $http->hasPostVariable( 'ExportAllButton' ) && $canEditExport || $http->hasPostVariable( 'FetchAllButton' ) && $canEditImport )
          && !eZSyndicationInstaller::missingTables() )
{
    $export = $http->hasPostVariable( 'ExportAllButton' );
    $error = '';
    $jobID = eZSyndicationJob::start( $export ? 'export' : 'import', $export ? array() : array( '--fetch-only' ), $error );
    if ( !$jobID )
    {
        eZSyndicationUI::notice( 'error', ezpI18n::tr( 'extension/syndication', 'The run could not be started: %reason', null, array( '%reason' => $error ) ) );
        return $module->redirectToView( 'menu' );
    }
    return $module->redirectToView( 'menu', array(), array(), array( 'job' => $jobID ) );
}

$summary = eZSyndicationDashboard::summary();
$recentFeeds = array();
$recentImports = array();
if ( !$summary['tables_missing'] )
{
    $recentFeeds = array_slice( (array)eZSyndicationFeed::fetchList( 0, 5 ), 0, 5 );
    $recentImports = array_slice( (array)eZSyndicationImport::fetchList( 0, 5 ), 0, 5 );
}

$tpl = eZTemplate::factory();
$tpl->setVariable( 'summary', $summary );
$tpl->setVariable( 'notices', eZSyndicationUI::takeNotices() );
$userParameters = isset( $Params['UserParameters'] ) ? $Params['UserParameters'] : array();
$tpl->setVariable( 'job_id', isset( $userParameters['job'] ) && eZSyndicationJob::isID( $userParameters['job'] ) ? $userParameters['job'] : '' );
$tpl->setVariable( 'recent_feeds', $recentFeeds );
$tpl->setVariable( 'recent_imports', $recentImports );
$tpl->setVariable( 'can_install', $canEditExport && $canEditImport );
$tpl->setVariable( 'can_edit_export', $canEditExport );
$tpl->setVariable( 'can_edit_import', $canEditImport );
$tpl->setVariable( 'can_create_feed', eZSyndicationFeed::canCreate() );
$tpl->setVariable( 'can_create_import', eZSyndicationImport::canCreate() );

$Result = array();
$Result['content'] = $tpl->fetch( 'design:syndication/menu.tpl' );
$Result['path'] = array( array( 'url' => false,
                                'text' => ezpI18n::tr( 'extension/syndication', 'Syndication' ) ) );

?>
