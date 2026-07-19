<?php
//
// Created on: <12-Sep-2004 16:08 kk>
//
// Copyright (C) 1999-2008 eZ Systems AS. All rights reserved.
//
// This source file is part of the eZ Publish (tm) Open Source Content
// Management System.
//
// This file may be distributed and/or modified under the terms of the
// "GNU General Public License" version 2 as published by the Free
// Software Foundation and appearing in the file LICENSE.GPL included in
// the packaging of this file.
//
// Licencees holding valid "eZ Publish professional licences" may use this
// file in accordance with the "eZ Publish professional licence" Agreement
// provided with the Software.
//
// This file is provided AS IS with NO WARRANTY OF ANY KIND, INCLUDING
// THE WARRANTY OF DESIGN, MERCHANTABILITY AND FITNESS FOR A PARTICULAR
// PURPOSE.
//
// The "eZ Publish professional licence" is available at
// http://ez.no/products/licences/professional/. For pricing of this licence
// please contact us via e-mail to licence@ez.no. Further contact
// information is available at http://ez.no/home/contact/.
//
// The "GNU General Public License" (GPL) is available at
// http://www.gnu.org/copyleft/gpl.html.
//
// Contact licence@ez.no if any conditions of this licencing isn't clear to
// you.
//

/*! \file module.php
 */

$Module = array( 'name' => 'eZSyndication',
                 'variable_params' => true );

$ViewList = array();
$ViewList['menu'] = array(
    'functions' => array( 'menu' ),
    'default_navigation_part' => 'ezsyndicationpart',
    'script' => 'menu.php' );

$ViewList['list'] = array(
    'functions' => array( 'view_export' ),
    'default_navigation_part' => 'ezsyndicationpart',
    'unordered_params' => array( 'offset' => 'Offset' ),
    'script' => 'list.php' );

$ViewList['import_list'] = array(
    'functions' => array( 'view_export' ),
    'default_navigation_part' => 'ezsyndicationpart',
    'unordered_params' => array( 'offset' => 'Offset' ),
    'script' => 'import_list.php' );

$ViewList['import_edit'] = array(
    'functions' => array( 'edit_import' ),
    'default_navigation_part' => 'ezsyndicationpart',
    'params' => array( 'ImportID' ),
    'unordered_params' => array( 'step' => 'Step' ),
    'script' => 'import_edit.php' );

$ViewList['pending_edit'] = array(
    'functions' => array( 'import_object_status' ),
    'default_navigation_part' => 'ezsyndicationpart',
    'params' => array( 'ImportID' ),
    'unordered_params' => array( 'offset' => 'Offset',
                                 'status' => 'Status' ),
    'script' => 'pending_edit.php' );

$ViewList['edit'] = array(
    'functions' => array( 'edit_export' ),
    'default_navigation_part' => 'ezsyndicationpart',
    'params' => array( 'FeedID' ),
    'script' => 'edit.php' );

$ViewList['add_feed_source'] = array(
    'functions' => array( 'edit_export' ),
    'default_navigation_part' => 'ezsyndicationpart',
    'params' => array( 'FeedID', 'Step' ),
    'unordered_params' => array( 'source_type' => 'SourceType' ),
    'script' => 'add_feed_source.php' );

$ViewList['list_source_filter'] = array(
    'functions' => array( 'edit_export' ),
    'default_navigation_part' => 'ezsyndicationpart',
    'params' => array( 'SourceFeedID' ),
    'script' => 'list_source_filter.php' );

$ViewList['edit_source_filter'] = array(
    'functions' => array( 'edit_export' ),
    'default_navigation_part' => 'ezsyndicationpart',
    'params' => array( 'SourceFilterID' ),
    'script' => 'edit_source_filter.php' );

$ViewList['edit_import_filter'] = array(
    'functions' => array( 'edit_import' ),
    'default_navigation_part' => 'ezsyndicationpart',
    'params' => array( 'ImportFilterID' ),
    'script' => 'edit_import_filter.php',
    'single_post_actions' => array( 'StoreButton' => 'Store',
                                    'DiscardButton' => 'Discard' ) );

$ViewList['import_info'] = array(
    'functions' => array( 'import_view' ),
    'default_navigation_part' => 'ezsyndicationpart',
    'params' => array( 'ImportID' ),
    'script' => 'import_info',
    'single_post_actions' => array( 'ImportButton' => 'Import' ) );

$ViewList['feed_info'] = array(
    'functions' => array( 'view_export_info' ),
    'default_navigation_part' => 'ezsyndicationpart',
    'params' => array( 'FeedID' ) );

$FeedID = array(
    'name' => 'Feed',
    'values' => array() );

$FunctionList = array();
$FunctionList['import_object_status'] = array();

$FunctionList['view_export'] = array();

$FunctionList['edit_export'] = array();

$FunctionList['remove_feed'] = array();

$FunctionList['create_feed'] = array();

$FunctionList['menu'] = array();

$FunctionList['view_import'] = array();

$FunctionList['edit_import'] = array();

$FunctionList['view_export_info'] = array();

$FunctionList['create_import'] = array();

$FunctionList['fetch_feed'] = array( 'FeedID' => $FeedID);
?>
