<?php
//
// Definition of eZImportEditName class
//
// Created on: <14-Nov-2004 22:33:26 hovik>
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

/*! \file ezimporteditname.php
*/

/*!
  \class eZImportEditName ezimporteditname.php
  \brief The class eZImportEditName does

*/

class eZImportEditName extends eZImportEditWizard
{
    /*!
     Constructor
    */
    function __construct( &$tpl, &$module, $storageName = false )
    {
        parent::__construct( $tpl, $module, $storageName );
    }

    /*
     \reimp
    */
    function postCheck()
    {
        $valid = true;
        $syndicationImport = $this->attribute( 'syndication_import' );
        $name = '';
        $server = '';

        if ( $this->HTTP->hasPostVariable( 'Name' ) )
        {
            $name = trim( $this->HTTP->postVariable( 'Name' ) );
        }
        $syndicationImport->setAttribute( 'name', $name );
        if ( strlen( $name ) == 0 )
        {
            $this->WarningList[] = ezpI18n::tr( 'design/standard/syndication/edit', 'Please enter valid Name' );
            $valid = false;
        }

        if ( $this->HTTP->hasPostVariable( 'Server' ) )
        {
            $server = $this->HTTP->postVariable( 'Server' );
        }
        $syndicationImport->setAttribute( 'server', $server );

        $url = parse_url( $server );

        if ( !isset( $url['scheme'] ) ||
             !isset( $url['host'] ) )
        {
            $this->WarningList[] = ezpI18n::tr( 'design/standard/syndication/edit', 'Warning, server is not valid URL !' );
            return false;
        }

        // Get syndication feed list
        $client = $syndicationImport->attribute( 'soap_client' );
        $request = new eZSOAPRequest( "fetchSyndicationFeedList", "http://ez.no/syndication" );
        $response = $client->send( $request );
//7x
// var_dump( $response );
//	echo "<hr>\n\n";
	if( !is_int( $response ) )
	{
           $responseCode = $response->faultCode();
           $responseValue = $response->value();
           $this->TPL->setVariable( 'response', $response->value() );
	}
	else
	{
	   $responseCode = false;
	}

        if ( is_int( $response ) && $responseCode == false )
        {
            $this->WarningList[] = ezpI18n::tr( 'design/standard/syndication/edit',
                                           'Warning, SOAP feed list request to "%server" did not return a valid result',
                                           '',
                                           array( '%server' => $server ) );
            return false;
        }
	else
	{
	}

        // Get server HostID
        $request = new eZSOAPRequest( "hostID", "http://ez.no/syndication" );
        $response = $client->send( $request );
	//7x
	// var_dump($response);
        if ( is_int( $response ) && $responseCode == false )
        {
            $this->WarningList[] = ezpI18n::tr( 'design/standard/syndication/edit',
                                           'Warning, SOAP feed list request to "%server" did not return a valid result #2',
                                           '',
                                           array( '%server' => $server ) );
            return false;
        }
        $syndicationImport->setAttribute( 'host_id', $response->value() );
        $syndicationImport->sync();

        return $valid;
    }
}

?>
