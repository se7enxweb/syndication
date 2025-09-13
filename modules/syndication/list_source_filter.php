<?php
//
// Created on: <30-Sep-2004 11:35:04 kk>
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
// http://www.gnu.org/copyleft/gpl.html.
//
// Contact licence@ez.no if any conditions of this licencing isn't clear to
// you.
//

/*! \file list_source_filter.php
*/

$module = $Params['Module'];
$sourceID = $Params['SourceFeedID'];
$http = eZHttpTool::instance();
$userParameters =  $Params['UserParameters'];

$feedSource = eZSyndicationFeedSource::fetchDraft( $sourceID );

if ( isset( $userParameters['generate_drafts'] ) )
{
    eZSyndicationFeedSourceFilter::fetchDraftList( $sourceID );
    return $module->redirectToView( 'list_source_filter', array( $sourceID ) );
}

if ( $http->hasPostVariable( 'AddFilter' ) )
{
    $sourceFilter = eZSyndicationFeedSourceFilter::create( $sourceID,
                                                           $http->postVariable( 'FilterType' ) );
    $sourceFilter->store();

    return $module->redirectToView( 'edit_source_filter',
                                    array( $sourceFilter->attribute( 'id') ) );
}
else if ( $http->hasPostVariable( 'RemoveFilter' ) )
{
    if ( $http->hasPostVariable( 'RemoveFilterIDArray' )  )
    {
        foreach( $http->postVariable( 'RemoveFilterIDArray' ) as $filterID )
        {
            $filter = eZSyndicationFeedSourceFilter::fetchDraft( $filterID );
            $filter->removeDraft();
        }
    }
}
else if ( $http->hasPostVariable( 'Finnish' ) )
{
    return $module->redirectToView( 'edit',
                                    array( $feedSource->attribute( 'feed_id' ) ) );
}

/* Fetch syndication filters */
$syndicationINI = eZINI::instance( 'syndication.ini' );
$filterArray = array();
foreach( $syndicationINI->variable( 'SyndicationFilters', 'FilterArray' ) as $filterType )
{
    $filterClassName = 'eZFilter' . $filterType;
    $filterArray[] = array( 'type' => $filterType,
                            'name' => eval( 'return ' . $filterClassName . '::name();' ) );
}

include_once( 'kernel/common/template.php' );
$tpl = templateInit();
$tpl->setVariable( 'feed_source', $feedSource );
$tpl->setVariable( 'filter_array', $filterArray );
$tpl->setVariable( 'step', 3 );

$Result = array();
$Result['content'] = $tpl->fetch( 'design:syndication/list_source_filter.tpl' );
$Result['path'] = array( array( 'url' => false,
                                'text' => ezpI18n::tr( 'syndication/list', 'Syndication' ) ),
                         array( 'url' => false,
                                'text' => ezpI18n::tr( 'syndication/list', 'Edit' ) ),
                         array( 'url' => false,
                                'text' => ezpI18n::tr( 'syndication/list', 'Add Source' ) ),
                         array( 'url' => false,
                                'text' => ezpI18n::tr( 'syndication/list', 'Add Filter' ) ) );

?>
