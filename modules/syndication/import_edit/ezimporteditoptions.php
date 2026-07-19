<?php
//
// Definition of eZImportEditOptions class
//
// Created on: <31-May-2006 10:53:10 hovik>
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

/*! \file ezimporteditoptions.php
*/

/*!
  \class eZImportEditOptions ezimporteditoptions.php
  \brief The class eZImportEditOptions does

*/

class eZImportEditOptions extends eZImportEditWizard
{
    /*!
     Constructor
    */
    function __construct( &$tpl, &$module, $storageName = false )
    {
        parent::__construct( $tpl, $module, $storageName );
    }

    /*!
     \reimp
    */
    function postCheck()
    {
        $syndicationImport = $this->attribute( 'syndication_import' );

        if ( $this->HTTP->hasPostVariable( 'SelectedNodeIDArray' ) &&
             eZPreferences::value( 'BrowseType' ) == 'MainPlacement' )
        {
            $selectedNode = $this->HTTP->postVariable( 'SelectedNodeIDArray' );
            $selectedNode = $selectedNode[0];

            $syndicationImport->setAttribute( 'placement_node_id', $selectedNode );
            $syndicationImport->sync();

            return false;
        }
        else if ( $this->HTTP->hasPostVariable( 'SelectedNodeIDArray' ) &&
             eZPreferences::value( 'BrowseType' ) == 'RelatedPlacement' )
        {
            $selectedNode = $this->HTTP->postVariable( 'SelectedNodeIDArray' );
            $selectedNode = $selectedNode[0];

            $syndicationImport->setAttribute( 'related_node_id', $selectedNode );
            $syndicationImport->sync();

            return false;
        }
        else if ( $this->HTTP->hasPostVariable( 'BrowseCancelButton' ) )
        {
            return false;
        }

        $optionList = array( 'auto_import' => 'AutomaticImport',
                             'exclude_top_node' => 'ExcludeTopNode',
                             'original_placement' => 'OriginalPlacement',
                             'include_related_objects' => 'IncludeRelatedObjects',
                             'use_hidden_status' => 'UseHiddenStatus' );
        foreach( $optionList as $optionName => $httpName )
        {
            $syndicationImport->setOption( $optionName, $this->HTTP->hasPostVariable( $httpName ) );
        }
        $syndicationImport->sync();

        if ( $this->HTTP->hasPostVariable( 'BrowseNodeLocation' ) )
        {
            eZPreferences::setValue( 'BrowseType', 'MainPlacement' );
            eZContentBrowse::browse( array( 'action_name' => 'SyndicationSetImportPlacement',
                                            'from_page' => '/syndication/import_edit/' ),
                                     $this->Module );
            return false;

        }
        else if ( $this->HTTP->hasPostVariable( 'BrowseRelatedLocation' ) )
        {
            eZPreferences::setValue( 'BrowseType', 'RelatedPlacement' );
            eZContentBrowse::browse( array( 'action_name' => 'SyndicationSetImportPlacement',
                                            'from_page' => '/syndication/import_edit/' ),
                                     $this->Module );
            return false;

        }

        return true;
    }

    /*!
     \reimp
    */
    function attributes()
    {
        return eZImportEditWizard::attributes();
    }

    /*!
     \reimp
    */
    function attribute( $attr )
    {
        $retVal = null;
        switch( $attr )
        {
            default:
            {
                $retVal = eZImportEditWizard::attribute( $attr );
            } break;
        }

        return $retVal;
    }
}

?>
