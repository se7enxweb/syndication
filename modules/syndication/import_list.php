<?php
//
// Copyright (C) 1998 - 2026 7x & Exponential Foundation. All rights reserved.
// Copyright (C) 1999-2008 eZ Systems AS. All rights reserved.
//
// This file may be distributed and/or modified under the terms of the
// "GNU General Public License" version 2 (or any later version).
//

/*! \file import_list.php
    The imports: filter, sort, page, create, remove (with a confirmation step).
*/

$module = $Params['Module'];
if ( $redirect = eZSyndicationUI::redirectIfNotInstalled( $module ) )
{
    return $redirect;
}
$http = eZHTTPTool::instance();
$vp = eZSyndicationUI::listParameters( $Params, array( 'name', 'id', 'enabled' ) );
$confirmList = array();

if ( $http->hasPostVariable( 'Create' ) &&
     eZSyndicationImport::canCreate() )
{
    $import = eZSyndicationImport::create();
    $import->store();

    return $module->redirectToView( 'import_edit', array( $import->attribute( 'id' ) ) );
}
else if ( $http->hasPostVariable( 'FilterButton' ) )
{
    $vp['q'] = trim( (string)$http->postVariable( 'Filter' ) );
    $vp['offset'] = 0;
    return $module->redirectTo( eZSyndicationUI::listURL( 'import_list', $vp ) );
}
else if ( $http->hasPostVariable( 'Remove' ) &&
          eZSyndicationImport::canRemove() )
{
    $ids = $http->hasPostVariable( 'RemoveImportIDArray' ) ? (array)$http->postVariable( 'RemoveImportIDArray' ) : array();
    foreach ( $ids as $importID )
    {
        $import = eZSyndicationImport::fetch( (int)$importID );
        if ( $import )
        {
            $confirmList[] = $import;
        }
    }
    if ( !$confirmList )
    {
        eZSyndicationUI::notice( 'warning', ezpI18n::tr( 'extension/syndication', 'Select at least one import to remove.' ) );
        return $module->redirectTo( eZSyndicationUI::listURL( 'import_list', $vp ) );
    }
}
else if ( $http->hasPostVariable( 'ConfirmRemoveButton' ) &&
          eZSyndicationImport::canRemove() )
{
    $removed = 0;
    $ids = $http->hasPostVariable( 'RemoveImportIDArray' ) ? (array)$http->postVariable( 'RemoveImportIDArray' ) : array();
    foreach ( $ids as $importID )
    {
        if ( eZSyndicationImport::removeImport( (int)$importID ) )
        {
            ++$removed;
        }
    }
    eZSyndicationUI::notice( 'feedback', ezpI18n::tr( 'extension/syndication', '%count imports were removed. The content they imported stays.', null, array( '%count' => $removed ) ) );
    return $module->redirectTo( eZSyndicationUI::listURL( 'import_list', $vp, array( 'offset' => 0 ) ) );
}

$all = eZPersistentObject::fetchObjectList( eZSyndicationImport::definition(), null,
                                            array( 'status' => eZSyndicationImport::STATUS_PUBLISHED ) );
$total = 0;
$importList = eZSyndicationUI::page( $all, $vp, array( 'name', 'server', 'comment' ), $total );

$tpl = eZTemplate::factory();
$tpl->setVariable( 'import_list', $importList );
$tpl->setVariable( 'total', $total );
$tpl->setVariable( 'all_count', is_array( $all ) ? count( $all ) : 0 );
$tpl->setVariable( 'vp', $vp );
$tpl->setVariable( 'view_parameters', array( 'offset' => $vp['offset'], 'q' => rawurlencode( $vp['q'] ), 'sort' => $vp['sort'], 'order' => $vp['order'] ) );
$tpl->setVariable( 'confirm_list', $confirmList );
$tpl->setVariable( 'notices', eZSyndicationUI::takeNotices() );
$tpl->setVariable( 'can_create', eZSyndicationImport::canCreate() );
$tpl->setVariable( 'can_remove', eZSyndicationImport::canRemove() );

$Result = array();
$Result['content'] = $tpl->fetch( 'design:syndication/import_list.tpl' );
$Result['path'] = array( array( 'url' => 'syndication/menu',
                                'text' => ezpI18n::tr( 'extension/syndication', 'Syndication' ) ),
                         array( 'url' => false,
                                'text' => ezpI18n::tr( 'extension/syndication', 'Imports' ) ) );

?>
