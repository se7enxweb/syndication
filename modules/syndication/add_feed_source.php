<?php
//
// Definition of Add_feed_source class
//
// Created on: <30-Sep-2004 08:55:08 kk>
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

/*! \file add_feed_source.php
*/

$module = $Params['Module'];
$feedID = $Params['FeedID'];
$step = $Params['Step'];
if ( !$step )
{
    $step = 1;
}

$http = eZHttpTool::instance();
$syndicationAction = new SyndicationAction( $module );
$syndicationFeed = eZSyndicationFeed::fetchDraft( $feedID );

$template = '';

switch( (int)$step )
{
    case 1:
    {
        $template = 'design:syndication/add_feed_source/step_1.tpl';
    } break;

    case 2:
    {
        return eZContentBrowse::browse( array( 'action_name' => 'SyndicationFeedSourceBrowse',
                                               'description_template' => 'design:syndication/add_feed_source/browse.tpl',
                                               'from_page' => '/syndication/add_feed_source/' . $feedID . '/' . ( $step + 1 ) . '/source_type/' . $http->postVariable( 'SourceType' ) ),
                                        $module );
    } break;

    case 3:
    {
        $selectedNode = $http->postVariable( 'SelectedNodeIDArray' );
        $selectedNode = $selectedNode[0];

        $syndicationFeedSource = $syndicationFeed->addSource( $selectedNode, $Params['SourceType'] );
        return $module->redirectToView( 'list_source_filter',
                                        array( $syndicationFeedSource->attribute( 'id' ) ) );
    } break;
}

include_once( "kernel/common/template.php" );
$tpl = templateInit();
$tpl->setVariable( 'syndication_feed', $syndicationFeed );
$tpl->setVariable( 'feed_id', $feedID );
$tpl->setVariable( 'step', $step );
$tpl->setVariable( 'next_step', $step + 1 );

$Result = array();
$Result['content'] = $tpl->fetch( $template );
$Result['path'] = array( array( 'text' => ezpI18n::tr( 'syndication/list', 'Syndication' ) ),
                         array( 'text' => ezpI18n::tr( 'syndication/list', 'Edit' ) ),
                         array( 'text' => ezpI18n::tr( 'syndication/list', 'Add Source' ) ) );
?>
