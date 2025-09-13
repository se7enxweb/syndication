<?php
//
// Definition of eZImportEditWizard class
//
// Created on: <14-Nov-2004 13:00:22 hovik>
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

/*! \file ezimporteditwizard.php
*/

/*!
  \class eZImportEditWizard ezimporteditwizard.php
  \brief The class eZImportEditWizard does

*/

class eZImportEditWizard extends eZWizardBase
{
    /*!
     \reimp
     Constructor

     \param tpl
     \param module
     \param storage name
     \param import ID ( optional )
    */
    function __construct( &$tpl, &$module, $storageName = false )
    {
        parent::__construct( $tpl, $module, $storageName );
        $this->StepTemplateBase = 'design:syndication/import_edit/step';
        $this->WizardURL = '/syndication/import_edit';
    }

    /*!
     \reimp
    */
    function attributes()
    {
        return array_merge( eZWizardBase::attributes(), array( 'import_id',
                                                               'syndication_import' ) );
    }

    /*!
     \reimp
    */
    function hasAttribute( $attr )
    {
        return in_array( $attr, $this->attributes() ) ||
            $this->hasMetaData( $attr );
    }

    /*!
     \reimp
    */
    function attribute( $attr )
    {
        $result = false;
        switch( $attr )
        {
            case 'import_id':
            {
                $result = $this->variable( 'import_id' );
            } break;

            case 'syndication_import':
            {
                $importID = $this->attribute( 'import_id' );
                if ( !$importID )
                {
                    $syndicationImport = eZSyndicationImport::create();
                    $syndicationImport->store();
                    $this->setVariable( 'import_id', $syndicationImport->attribute( 'id' ) );
                    $result = $syndicationImport;
                }
                else
                {
                    $result = eZSyndicationImport::fetchDraft( $importID );
                }
            } break;

            default:
            {
                if ( $this->hasMetaData( $attr ) )
                {
                    $result = $this->metaData( $attr );
                }
                else
                {
                    $result = eZWizardBase::attribute( $attr );
                }
            } break;
        }

        return $result;
    }

    /*!
     Publish syndication, and finnish import wizard
    */
    function finish()
    {
        $syndicationImport = $this->attribute( 'syndication_import' );
        $syndicationImport->publish();

        $this->cleanup();
        return false;
    }

    /*!
     \reimp
    */
    function process()
    {
        $this->TPL->setVariable( 'syndication_import', $this->attribute( 'syndication_import' ) );
        $this->TPL->setVariable( 'wizard', $this );

        $Result = array();
        $Result['content'] = $this->TPL->fetch( 'design:syndication/import_edit/base.tpl' );
        $Result['path'] = array( array( 'url' => 'syndication/import_list',
                                        'text' => ezpI18n::tr( 'syndication/list', 'Syndication Import' ) ),
                                 array( 'url' => false,
                                        'text' => ezpI18n::tr( 'syndication/list', 'Edit' ) ) );
        return $Result;
    }

    /*!
     \reimp
    */
    static function instance( &$tpl, &$module, &$Params)
    {
        $basePath = eZExtension::baseDirectory() . '/syndication/modules/syndication/import_edit/';
        $stepArray = array( array( 'class' => 'eZImportEditName',
                                   'file' => 'ezimporteditname.php' ),
                            array( 'class' => 'eZImportEditFeed',
                                   'file' => 'ezimporteditfeed.php' ),
                            array( 'class' => 'eZImportEditFilter',
                                   'file' => 'ezimporteditfilter.php'  ),
                            array( 'class' => 'eZImportEditOptions',
                                   'file' => 'ezimporteditoptions.php' ),
                            array( 'class' => 'eZImportEditFinal',
                                   'file' => 'ezimporteditfinal.php' ),
                            array( 'class' => 'eZImportEditWizard',
                                   'file' => 'ezimporteditwizard.php',
                                   'operation' => 'finish' ) );

        $wizardClass = eZWizardBaseClassLoader::createClass( $tpl,
                                                             $module,
                                                             $stepArray,
                                                             $basePath,
                                                             'eZImportWizard' );

        // If ImportID is set, this is the first step in the wizard.
        if ( isset ( $Params['ImportID'] ) )
        {
            if ( is_numeric( $Params['ImportID'] ) )
            {
                $wizardClass->cleanup();
                $wizardClass->setVariable( 'import_id', $Params['ImportID'] );
                $wizardClass->setMetaData( 'current_stage', eZWizardBase::STAGE_PRE );
                $wizardClass->setMetaData( 'current_step', 0 );

                eZDebug::writeNotice( 'Set Import ID: ' . $Params['ImportID'],
                                      'eZImportEditWizard::instance()' );
            }
            else
            {
                // Invalid import ID param.
                return false;
            }
        }

        return $wizardClass;
    }
}

?>
